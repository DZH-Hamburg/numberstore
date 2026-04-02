<?php

namespace App\Enums;

enum GroupMembershipRole: string
{
    case GroupCreator = 'group_creator';
    case Consumer = 'consumer';
}
