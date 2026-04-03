import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

async function readStdin() {
  const chunks = [];
  for await (const chunk of process.stdin) chunks.push(chunk);
  const raw = Buffer.concat(chunks).toString('utf8').trim();
  if (!raw) return {};
  return JSON.parse(raw);
}

function isNonEmptyString(v) {
  return typeof v === 'string' && v.trim() !== '';
}

async function ensureDirForFile(filePath) {
  await fs.mkdir(path.dirname(filePath), { recursive: true });
}

function pickWaitUntil(value) {
  if (value === 'load' || value === 'domcontentloaded' || value === 'networkidle') return value;
  return 'networkidle';
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Seite schrittweise durchscrollen, damit Lazy-Loading/Infinite-Scroll Inhalte geladen werden,
 * bevor ein Full-Page-Screenshot erstellt wird.
 */
async function scrollDocumentToTriggerLazyLoads(page, timeoutMs) {
  const stepMs = 80;
  const maxSteps = 250;
  const settleMs = Math.min(12_000, Math.max(2000, timeoutMs));
  for (let i = 0; i < maxSteps; i += 1) {
    const atBottom = await page.evaluate(() => {
      const el = document.scrollingElement ?? document.documentElement;
      return el.scrollTop + el.clientHeight >= el.scrollHeight - 2;
    });
    if (atBottom) break;
    await page.evaluate(() => {
      const step = Math.min(window.innerHeight, 900);
      window.scrollBy(0, step);
    });
    await sleep(stepMs);
  }
  await page.evaluate(() => window.scrollTo(0, 0));
  try {
    await page.waitForLoadState('networkidle', { timeout: settleMs });
  } catch {
    /* weiter ohne harte Fehler — SPA / lange Polls */
  }
}

function coalesceFullPage(input) {
  const v = input?.fullPage ?? input?.full_page;
  if (v === undefined || v === null) return true;
  if (typeof v === 'boolean') return v;
  if (typeof v === 'number') return v !== 0;
  if (typeof v === 'string') {
    const s = v.trim().toLowerCase();
    if (s === '' || s === ' ' || s === '0' || s === 'false' || s === 'no' || s === 'off') return false;
    return true;
  }
  return Boolean(v);
}

/** CSS-OR-Kette für typische Login-Usernamenfelder (nur wenn Secret gesetzt, kein expliziter Selector). */
const USERNAME_FALLBACK =
  'input[type="email"], input[name*="email" i], input[name*="user" i], input[name*="login" i], input[id*="email" i], input[id*="user" i], input[id*="login" i]';

const PASSWORD_FALLBACK = 'input[type="password"]';

/** Häufige TOTP-/Einmalcode-Felder nach dem ersten Login-Schritt. */
const TOTP_FALLBACK =
  'input[type="tel"], input[name*="otp" i], input[name*="code" i], input[autocomplete="one-time-code"], input[id*="otp" i], input[id*="totp" i], input[placeholder*="code" i]';

/**
 * Nach Klick auf „Anmelden“ / Navigation (auch SPA): DOM ready, dann optional networkidle.
 */
async function settleAfterAction(page, timeoutMs) {
  const t = Math.max(5000, Math.min(timeoutMs, 60000));
  try {
    await page.waitForLoadState('domcontentloaded', { timeout: t });
  } catch {
    /* still try networkidle */
  }
  try {
    await page.waitForLoadState('networkidle', { timeout: Math.min(t, 28000) });
  } catch {
    /* SPA / lange Polls: domcontentloaded reicht oft */
  }
}

async function fillVisibleFirst(page, selectorOrFallback, value, timeoutMs, label) {
  if (!isNonEmptyString(value)) return;
  if (!isNonEmptyString(selectorOrFallback)) {
    throw new Error(`Screenshot: ${label} ist gesetzt, aber kein Selektor ermittelbar. Bitte CSS-Selector im Element konfigurieren.`);
  }
  const loc = page.locator(selectorOrFallback).first();
  await loc.waitFor({ state: 'visible', timeout: timeoutMs });
  await loc.fill(value);
}

async function trySubmit(page, explicitSubmitSel, timeoutMs) {
  if (isNonEmptyString(explicitSubmitSel)) {
    await page.locator(explicitSubmitSel).first().click({ timeout: timeoutMs });
    return;
  }
  const generic = page.locator('button[type="submit"], input[type="submit"]').first();
  try {
    await generic.click({ timeout: Math.min(timeoutMs, 8000) });
  } catch {
    await page.keyboard.press('Enter');
  }
}

/**
 * Wenn Anmeldedaten (username/password) gesetzt sind: Formular füllen, ersten Login auslösen, warten.
 */
async function runPasswordLoginStep(page, selectors, username, password, timeoutMs) {
  const hasUser = isNonEmptyString(username);
  const hasPass = isNonEmptyString(password);
  if (!hasUser && !hasPass) {
    return;
  }

  const userSel = isNonEmptyString(selectors.username) ? selectors.username : hasUser ? USERNAME_FALLBACK : null;
  const passSel = isNonEmptyString(selectors.password) ? selectors.password : hasPass ? PASSWORD_FALLBACK : null;

  if (hasUser) {
    await fillVisibleFirst(page, userSel, username, timeoutMs, 'Benutzername');
  }
  if (hasPass) {
    await fillVisibleFirst(page, passSel, password, timeoutMs, 'Passwort');
  }

  await trySubmit(page, selectors.submit, timeoutMs);
  await settleAfterAction(page, timeoutMs);
}

/**
 * Zweiter Schritt (TOTP), erst nach dem ersten Login.
 */
async function runTotpStep(page, selectors, totpCode, timeoutMs) {
  if (!isNonEmptyString(totpCode)) {
    return;
  }

  const totpSel = isNonEmptyString(selectors.totp) ? selectors.totp : TOTP_FALLBACK;
  const loc = page.locator(totpSel).first();
  await loc.waitFor({ state: 'visible', timeout: timeoutMs });
  await loc.fill(totpCode);

  const secondSubmit = isNonEmptyString(selectors.totp_submit) ? selectors.totp_submit : selectors.submit;
  await trySubmit(page, secondSubmit, timeoutMs);
  await settleAfterAction(page, timeoutMs);
}

async function main() {
  const input = await readStdin();

  const url = input?.url;
  const outputPath = input?.outputPath;
  if (!isNonEmptyString(url)) throw new Error('Missing "url".');
  if (!isNonEmptyString(outputPath)) throw new Error('Missing "outputPath".');

  const selectors = input?.selectors ?? {};
  const username = input?.username;
  const password = input?.password;
  const totpCode = input?.totpCode;

  const timeoutMs = Number.isFinite(input?.timeoutMs) ? input.timeoutMs : 60000;
  const waitUntil = pickWaitUntil(input?.waitUntil);
  const waitFor = input?.waitFor;
  const fullPage = coalesceFullPage(input);

  await ensureDirForFile(outputPath);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  page.setDefaultTimeout(timeoutMs);

  try {
    await page.goto(url, { waitUntil });

    await runPasswordLoginStep(page, selectors, username, password, timeoutMs);
    await runTotpStep(page, selectors, totpCode, timeoutMs);

    if (isNonEmptyString(waitFor)) {
      await page.waitForSelector(waitFor, { state: 'visible' });
    }

    if (fullPage) {
      await scrollDocumentToTriggerLazyLoads(page, timeoutMs);
    }

    await page.screenshot({ path: outputPath, fullPage });

    process.stdout.write(JSON.stringify({ ok: true, outputPath }));
  } finally {
    await page.close().catch(() => {});
    await browser.close().catch(() => {});
  }
}

main().catch((err) => {
  process.stderr.write(String(err?.stack ?? err?.message ?? err) + '\n');
  process.exit(1);
});
