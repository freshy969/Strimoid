<?php namespace Strimoid\Providers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Strimoid\Models\Comment;
use Strimoid\Models\CommentReply;
use Strimoid\Models\Content;
use Strimoid\Models\ContentRelated;
use Strimoid\Models\Conversation;
use Strimoid\Models\Entry;
use Strimoid\Models\EntryReply;
use Strimoid\Models\Group;
use Strimoid\Models\Notification;
use Strimoid\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vinkla\Hashids\Facades\Hashids;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Strimoid\Http\Controllers';

    public function boot()
    {
        parent::boot();

        $router = $this->app->make(Router::class);

        $this->bindModel($router, 'content', Content::class);
        $this->bindModel($router, 'related', ContentRelated::class);
        $this->bindModel($router, 'notification', Notification::class);
        $this->bindModel($router, 'comment', Comment::class);
        $this->bindModel($router, 'comment_reply', CommentReply::class);
        $this->bindModel($router, 'entry', Entry::class);
        $this->bindModel($router, 'entry_reply', EntryReply::class);
        $this->bindModel($router, 'group', Group::class);
        $this->bindModel($router, 'user', User::class);
        $this->bindModel($router, 'conversation', Conversation::class);
    }

    /**
     * Bind object resolve function for given model class.
     */
    public function bindModel(Router $router, string $key, string $className)
    {
        $binding = function ($value) use ($className) {
            try {
                if (ends_with($className, ['Group', 'User'])) {
                    return $className::name($value)->firstOrFail();
                }

                $ids = Hashids::decode($value);

                if (!count($ids)) {
                    abort(404);
                }

                return $className::findOrFail($ids[0]);
            } catch (ModelNotFoundException $e) {
                throw new NotFoundHttpException();
            }
        };

        $router->bind($key, $binding);
    }

    public function map()
    {
        \Route::group([
            'middleware' => 'web',
            'namespace'  => $this->namespace,
        ], function ($router) {
            require app_path('Http/routes.php');
        });

        \Route::group([
            'namespace'  => $this->namespace,
        ], function($router) {
            \Route::get('/i/duck/{username}', 'DuckController@drawDuck');
            \Route::get('/i/{width}x{height}/{folder}/{filename}.{format}', 'ImageController@resizeImage')
                ->where(['format' => '\w{3}']);
            \Route::get('/i/{folder}/{filename}.{format}', 'ImageController@showImage')
                ->where(['format' => '\w{3}']);
        });
    }
}
