<?php

namespace App\Http\Controllers;

trait CustomTraitAuth
{
    // Function to check if the user is an owner of a client, or has access through cooperation
    public function owneroraccess($userid, $clientid){
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // If the user is the owner of the client, set a flag
        if ($client->user_id == $user->id) {
            $owner = true;
        }

        // If the user is NOT the owner of the client, set a flag
        if ($client->user_id !== $user->id) {
            $owner = false;
        }

        // If the user is a system admin, set a flag
        if ($user->role == 2) {
            $admin = true;
        }

        // If the user is NOT a system admin, set a flag
        if ($user->role !== 2) {
            $admin = false;
        }

        // Check if this user has access to client through cooperation ($coopclients is true if the user has access throug coop)
        $coopclients = $user->coopclients()->where('client_id', $client->id)->first();

        // If the user is given rights by owner, redirect with error
        if (!$owner AND !$coopclients AND !$admin) {
            return false;
        }

        return true;
    }

    // Function to check if the user is an owner of a client
    public function owner($userid, $clientid){
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // If the user is a system admin, set a flag
        if ($user->role == 2) {
            $admin = true;
        }

        // If the user is NOT a system admin, set a flag
        if ($user->role !== 2) {
            $admin = false;
        }

        // If the user is the owner of the client, set a flag
        if ($client->user_id == $user->id) {
            $owner = true;
        }

        // If the user is NOT the owner of the client, set a flag
        if ($client->user_id !== $user->id) {
            $owner = false;
        }

        // If the user is neither owner or system admin, return false
        if (!$owner AND !$admin) {
            return false;
        }

        return true;
    }

    // Function to check if the user is the writer of a record
    public function writer($userid, $recordid){
        $user = \App\User::find($userid);
        $record = \App\Record::find($recordid);

        // If the user is a system admin, set a flag
        if ($user->role == 2) {
            return true;
        }

        // If the user is the writer, set a flag
        if ($user->id == $record->created_by) {
            return true;
        }

        // If the user is NOT the writer, set a flag
        if ($user->id !== $record->created_by) {
            return false;
        }
    }

    // Function to check if the user is the writer of a record
    public function allowedversionhistory($userid, $changerecordid){
        $user = \App\User::find($userid);
        $record = \App\Changerecordlog::find($changerecordid);

        // If the user is a system admin, set a flag
        if ($user->role == 2) {
            return true;
        }

        // If the user is the writer, set a flag
        if ($user->id == $record->created_by) {
            return true;
        }

        // If the user is NOT the writer, set a flag
        if ($user->id !== $record->created_by) {
            return false;
        }
    }

    // Function to check if the user is an owner of a client
    public function incompany($loggedinuserid, $userid){
        $loggedinuser = \App\User::find($loggedinuserid);
        $coop = \App\User::find($userid);

        // If the user is trying to transfer to himself, set a flag
        if ($loggedinuser->id == $coop->id) {
            return false;
        }

        // If the user is a system admin, set a flag
        if ($loggedinuser->role == 2) {
            $admin = true;
        }

        // If the user is NOT a system admin, set a flag
        if ($loggedinuser->role !== 2) {
            $admin = false;
        }

        // If the user is in the loggedinusers company, set a flag
        if ($loggedinuser->company_id == $coop->company->id) {
            return true;
        }

        // If the user is NOT in the loggedinusers company, set a flag
        if ($loggedinuser->company_id !== $coop->company->id) {
            return false;
        }
    }

    // Function to check if the user is an owner of a client, or has access through cooperation
    public function cooperativeaccess($userid, $clientid){
        $user = \App\User::find($userid);
        $client = \App\Client::find($clientid);

        // Check if this user has access to client through cooperation ($coopclients is true if the user has access throug coop)
        $coopclients = $user->coopclients()->where('client_id', $client->id)->first();

        // If the user is given rights by owner, redirect with error
        if ($coopclients) {
            return true;
        }

        return false;
    }

}
