<?php

namespace Whilesmart\Organizations\Enums;

enum OrganizationAction: string
{
    case INDEX = 'index';
    case STORE = 'store';
    case SHOW = 'show';
    case UPDATE = 'update';
    case DESTROY = 'destroy';
    case ADD_MEMBER = 'addMember';
    case GET_MEMBERS = 'getMembers';
    case REMOVE_MEMBER = 'removeMember';
}
