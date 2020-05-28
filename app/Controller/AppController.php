<?php


namespace App\Controller;

use App;
use Core\Controller\Controller;
use Core\HTML\BootstrapForm;
use Core\Session\Session;

class AppController extends Controller
{
    protected $template = 'default';

    public function __construct()
    {
        $this->viewPath = ROOT . '/app/Views/';
    }

    protected function sortComments()
    {
        $this->loadModel('Comment');
        $comments = $this->Comment->validCommentsByPosts($_GET['id']);
        if (!empty($comments)) {
            $comments_by_id = [];

            foreach ($comments as $comment) {
                $comments_by_id[$comment->id] = $comment;
            }
            foreach ($comments as $k => $comment) {
                if ($comment->parent_id != 0) {
                    $comments_by_id[$comment->parent_id]->children[] = $comment;
                    unset($comments[$k]);
                }
            }
        }
        return $comments;
    }

    protected function loadModel($modelName)
    {
        $this->$modelName = App::getInstance()->getTable($modelName);
    }

    protected function showComments($path, $articles, $comments = null, $isForm = true)
    {
        $form = new BootstrapForm($_POST);
        if ($isForm) {
            if ((isset($_POST['content'])) && (!empty($_POST['content']))) {
                $this->comment();
                $this->render($path, compact('articles', 'comments', 'form'));
            } else {
                $this->render($path, compact('articles', 'comments', 'form'));
            }
        } elseif ($comments == null) {
            if ($isForm) {
                $this->comment();
                $form = new BootstrapForm($_POST);
                $this->render($path, compact('articles', 'form'));
            } else {
                $this->render($path, compact('articles'));
            }
        }
    }
    private function comment()
    {
        $this->loadModel('Comment');
        if ((isset($_POST['content'])) && (!empty($_POST['content']))) {
            $user = Session::getInstance()->read('auth');
            $this->Comment->submitComment($_POST['content'], $user->username, $_GET['id']);
            Session::getInstance()->setFlash('success', 'Votre commentaire est en attente de validation');
            App::getInstance()->redirect('posts.show&id=' . $_GET['id']);
        }
    }
}