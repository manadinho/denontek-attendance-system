<?php

function checkAuth()
{
    return isOwner() || isUser();
}

function user()
{
    if(checkAuth()) {
        return isOwner() ? auth()->guard('owner')->user() : auth()->guard('web')->user();
    }
}

function isOwner()
{
    return auth()->guard('owner')->check();
}

function isUser()
{
    return auth()->guard('web')->check();
}

function userType()
{
    $type = 'owner';
    if(auth()->guard('web')->check()) {
        $type = auth()->guard('web')->user()->type;
    }
    return $type;
}

function currentGuard()
{
    return isOwner() ? 'owner' : 'web';
}