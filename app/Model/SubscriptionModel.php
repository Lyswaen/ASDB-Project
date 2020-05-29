<?php


namespace App\Model;


use Core\Mail\Mail;
use Core\Table\Table;

class SubscriptionModel extends Table
{
    public function subscribe($user_id, $category_id)
    {
        $this->query('INSERT INTO subscription SET user_id = ?, category_id = ?', [$user_id, $category_id]);
    }

    public function isAlreadySubscribed($user, $category_id)
    {
        return $this->query('SELECT * FROM subscription WHERE user_id = ? AND category_id = ?', [$user->id, $category_id], true);
    }

    public function whoIsSubscribed($category_id)
    {

        $entries = $this->query('SELECT user_id FROM subscription WHERE category_id = ?', [$category_id], false, false);

        foreach ($entries as $user) {
            $users[] = $this->query('SELECT * FROM users WHERE id = ?', [$user->user_id], true, false);
        }
        return $users;
    }

    public function alertUser($users, $category)
    {
        foreach ($users as $user) {
            Mail::sendMail([
                'email' => $user->email,
                'subject' => 'Un nouvel article vous attend',
                'message' => "Coucou $user->username, j'ai une bonne nouvelle !! <br><br> Un nouvel article est apparu dans la categorie <em><strong>$category->titre</strong></em>, je vous souhaite une bonne lecture =D"
            ]);
        }
    }
}