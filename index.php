<?php

ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);









$requestUrl = isset($_GET['url']) ? $_GET['url'] : '/';
$SP = isset($_GET['puid']) ? $_GET['puid'] : '';

// Include connection and login check for all pages except when explicitly handling differently



$path = 'pages/' . $requestUrl . '.php';



switch ($requestUrl) {
    case '/':
    case 'Home':
        include_once 'inc/conn.php';
        include_once 'parts/header.php';
        //   CheckUserLogIn($base_url);
        include_once 'pages/Home.php';
        include_once 'parts/footer.php';
        break;

    case 'B':
    case 'M':
        include_once 'inc/conn.php';
        include_once $path;
        break;

    case 'blog-details':
        include_once 'inc/conn.php';

        // Pre-fetch post BEFORE header so SEO meta tags pick up the post's values
        $_blogSlug = $_GET['puid'] ?? '';
        $post = null;
        if ($_blogSlug !== '' && isset($pdo) && $pdo instanceof PDO) {
            $_stmt = $pdo->prepare(
                "SELECT p.*, c.name_ar AS cat_name, c.slug AS cat_slug,
                        a.full_name AS author_name, a.avatar AS author_avatar
                 FROM blog_posts p
                 LEFT JOIN blog_categories c ON c.id = p.category_id
                 LEFT JOIN admins a ON a.id = p.author_id
                 WHERE p.slug = ? AND p.status = 'published' LIMIT 1"
            );
            $_stmt->execute([$_blogSlug]);
            $post = $_stmt->fetch();

            if ($post) {
                $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
                $PageTitle = $post['meta_title_ar'] ?: $post['title_ar'];
                $_metaDesc = mb_substr(strip_tags($post['meta_description_ar'] ?: $post['excerpt_ar'] ?: $post['title_ar']), 0, 160);
                $escapedDescription = htmlspecialchars($_metaDesc, ENT_QUOTES, 'UTF-8');
                $KeyWords = htmlspecialchars($post['meta_keywords_ar'] ?? '', ENT_QUOTES, 'UTF-8');
                $ogImage = $post['featured_image'] ?: ($base_url . 'assets/favicon.ico');
            }
        }

        include_once 'parts/header.php';
        include_once 'pages/blog-details.php';
        include_once 'parts/footer.php';
        break;





    case 'blog':
        include_once 'inc/conn.php';
        include_once 'parts/header.php';
        include_once 'pages/blog.php';
        include_once 'parts/footer.php';
        break;

    default:
        if (file_exists($path)) {

            include_once 'inc/conn.php';
            include_once 'parts/header.php';
            //   CheckUserLogIn($base_url);

            include_once $path;
            include_once 'parts/footer.php';
        } else {
            include_once 'inc/conn.php';
            include_once 'parts/header.php';
            include_once 'pages/404.php';
            include_once 'parts/footer.php';
        }
        break;
}


// $conn->close();
// ob_end_flush();

