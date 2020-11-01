<?php

namespace App\Http\Traits;
use App\Client;
use App\User;
use App\Changerecordlog;
use App\Record;


trait CustomAuthTrait
{
    // Function to check if the user is an owner of a client, or has access through cooperation
    public function ownerOrAccess($userId, $clientId) {
        $user = User::find($userId);
        $client = Client::find($clientId);

        // If the user is the owner of the client, set a flag
        if ($client->user_id === $user->id) {
            $owner = true;
        }

        // If the user is NOT the owner of the client, set a flag
        if ($client->user_id !== $user->id) {
            $owner = false;
        }

        // If the user is a system admin, set a flag
        if ($user->role === 2) {
            $admin = true;
        }

        // If the user is NOT a system admin, set a flag
        if ($user->role !== 2) {
            $admin = false;
        }

        // Check if this user has access to client through cooperation ($coopclients is true if the user has access throug coop)
        $coopClients = $user->coopclients()->where('client_id', $client->id)->first();

        // If the user is given rights by owner, redirect with error
        if (!$owner && !$coopClients && !$admin) {
            return false;
        }

        return true;
    }

    // Function to check if the user is an owner of a client
    public function owner($userId, $clientId) {
        $user = User::find($userId);
        $client = Client::find($clientId);
        $admin = false;
        $owner = false;

        // If the user is a system admin, set a flag
        if ($user->role === 2) {
            $admin = true;
        }

        // If the user is the owner of the client, set a flag
        if ($client->user_id === $user->id) {
            $owner = true;
        }

        // If the user is neither owner nor system admin, return false
        if (!$owner && !$admin) {
            return false;
        }

        return true;
    }

    // Function to check if the user is the writer of a record
    public function writer($userId, $recordId) {
        $user = User::find($userId);
        $record = Record::find($recordId);

        // If the user is a system admin, set a flag
        if ($user->role === 2) {
            return true;
        }

        // If the user is the writer, set a flag
        if ($user->id === $record->created_by) {
            return true;
        }

        // If the user is NOT the writer, set a flag
        return false;
    }

    // Function to check if the user is the writer of a record
    public function allowedVersionHistory($userId, $changeRecordId) {
        $user = User::find($userId);
        $record = Changerecordlog::find($changeRecordId);

        // If the user is a system admin, set a flag
        if ($user->role === 2) {
            return true;
        }

        // If the user is the writer, set a flag
        if ($user->id === $record->created_by) {
            return true;
        }

        // If the user is NOT the writer, set a flag
        if ($user->id !== $record->created_by) {
            return false;
        }
    }

    // Function to check if the user is an owner of a client
    public function inCompany($loggedInUserId, $userId) {
        $loggedInUser = User::find($loggedInUserId);
        $coop = User::find($userId);

        // If the user is trying to transfer to himself, set a flag
        if ($loggedInUser->id === $coop->id) {
            return false;
        }

        // If the user is in the loggedinusers company, set a flag
        if ($loggedInUser->company_id === $coop->company->id) {
            return true;
        }

        // If the user is a system admin, set a flag
        if ($loggedInUser->role === 2) {
            return true;
        }

        // If the user is NOT a system admin, set a flag
        if ($loggedInUser->role !== 2) {
            return false;
        }

        return false;
    }

    // Function to check if the user is an owner of a client, or has access through cooperation
    public function cooperativeAccess($userId, $clientId) {
        $user = User::find($userId);
        $client = Client::find($clientId);

        // Check if this user has access to client through cooperation ($coopclients is true if the user has access through coop)
        $coopClients = $user->coopclients()->where('client_id', $client->id)->first();

        // If the user is given rights by owner, redirect with error
        if ($coopClients) {
            return true;
        }

        return false;
    }

    public function checkSuspended($userId, $redirect) {
        $user = User::find($userId);

        if ($user->suspended && $redirect) {
            return redirect('/')->with('message', 'Tilgangen din har blitt begrenset på grunn av mangelfull betaling')->send();
        }
        return $user->suspended;
    }

    public function checkPaymentMissing($userId) {
        $user = User::find($userId);

        return $user->payment_missing;
    }
}
