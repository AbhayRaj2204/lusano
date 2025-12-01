<?php
// Minimal share endpoint to provide crawlable Open Graph meta for Slack/WhatsApp, then redirect users to UI page.

header('Content-Type: text/html; charset=utf-8');

$type = isset($_GET['type']) && in_array($_GET['type'], ['digital', 'hardware']) ? $_GET['type'] : 'digital';
$id   = isset($_GET['id']) ? $_GET['id'] : null;

// Build origin and project base path (e.g. /mythic/)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$origin = $scheme . '://' . $host;
// Example: SCRIPT_NAME => /mythic/share/index.php
$projectBasePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/'; // => /mythic/

function absolute_url($path, $origin, $projectBasePath) {
  if (!$path) return '';
  if (preg_match('#^https?://#i', $path)) return $path;
  $path = ltrim($path, '/');
  return $origin . $projectBasePath . $path;
}

function filesystem_root() {
  // Path to the project root on disk (two levels up from this file)
  return dirname(__DIR__);
}

// Load and parse CSVs similar to api/get-products.php (simplified for one product)
function parse_csv($filePath) {
  if (!file_exists($filePath)) return [];
  $out = [];
  if (($h = fopen($filePath, 'r')) !== false) {
    $headers = fgetcsv($h);
    while (($row = fgetcsv($h)) !== false) {
      if (count($row) === count($headers)) {
        $out[] = array_combine($headers, $row);
      }
    }
    fclose($h);
  }
  return $out;
}

function load_product_images($productId, $productType, $root) {
  $file = $root . '/admin/data/product_images.csv';
  $images = [];
  if (!file_exists($file)) return $images;

  if (($h = fopen($file, 'r')) !== false) {
    $headers = fgetcsv($h);
    while (($row = fgetcsv($h)) !== false) {
      if (count($row) === count($headers)) {
        $img = array_combine($headers, $row);
        if ($img['product_id'] === $productId && $img['product_type'] === $productType && $img['status'] === 'active') {
          $imagePath = $img['image_path'];
          if (strpos($imagePath, '/') === 0) $imagePath = substr($imagePath, 1);
          if (strpos($imagePath, 'admin/') !== 0 && strpos($imagePath, 'placeholder.svg') === false) {
            $imagePath = 'admin/' . $imagePath;
          }
          $images[] = [
            'path' => $imagePath,
            'alt'  => $img['alt_text'] ?? '',
            'sort_order' => (int)($img['sort_order'] ?? 0),
          ];
        }
      }
    }
    fclose($h);
  }
  usort($images, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
  return $images;
}

function load_product($type, $id, $root) {
  if (!$id) return null;
  if ($type === 'hardware') {
    $csv = $root . '/admin/data/hardware_products.csv';
    if (!file_exists($csv)) $csv = $root . '/data/hardware_products.csv';
  } else {
    $csv = $root . '/admin/data/digital_products.csv';
    if (!file_exists($csv)) $csv = $root . '/data/digital_products.csv';
  }
  $rows = parse_csv($csv);
  $product = null;
  foreach ($rows as $p) {
    if (($p['id'] ?? '') === $id) {
      // normalize main image path like the API
      if (!empty($p['image'])) {
        $img = $p['image'];
        if (strpos($img, '/') === 0) $img = substr($img, 1);
        if (strpos($img, 'admin/') !== 0) $img = 'admin/' . $img;
        $p['image'] = $img;
      }
      $product = $p;
      break;
    }
  }
  if ($product) {
    $images = load_product_images($id, $type, $root);
    $product['images'] = $images;
  }
  return $product;
}

$root = filesystem_root();
$product = load_product($type, $id, $root);

// Derive title/description/image
$title = 'LUSANO Product';
$desc  = 'Explore LUSANO product details including key features, specifications, and installation.';
$image = '';

if ($product) {
  $title = $product['title'] ?? $title;
  $desc  = ($product['tagline'] ?? $desc) ?: $desc;

  if (!empty($product['images']) && is_array($product['images'])) {
    $image = $product['images'][0]['path'] ?? '';
  }
  if (!$image && !empty($product['image'])) {
    $image = $product['image'];
  }
}

if (!$image) {
  // final fallback
  $image = ($type === 'hardware') ? 'computer-hardware.png' : 'diverse-products-still-life.png';
}

$qpTitle = isset($_GET['title']) ? trim((string)$_GET['title']) : '';
$qpDesc  = isset($_GET['desc']) ? trim((string)$_GET['desc']) : '';
$qpImg   = isset($_GET['img']) ? trim((string)$_GET['img']) : '';
if ($qpTitle !== '') { $title = $qpTitle; }
if ($qpDesc  !== '') { $desc  = $qpDesc; }
if ($qpImg   !== '') { $image = $qpImg; }

$absImage = absolute_url($image, $origin, $projectBasePath);
$shareUrl = $origin . $_SERVER['REQUEST_URI'];
$target   = $origin . $projectBasePath . ($type === 'hardware' ? 'hardware-product-details.html' : 'product-details.html') . '?id=' . urlencode($id ?: '');

// Image type detection for better OG metadata
$imgLower = strtolower(parse_url($absImage, PHP_URL_PATH) ?? '');
$imgType = 'image/jpeg';
if (str_ends_with($imgLower, '.png')) $imgType = 'image/png';
elseif (str_ends_with($imgLower, '.webp')) $imgType = 'image/webp';
elseif (str_ends_with($imgLower, '.gif')) $imgType = 'image/gif';

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($title); ?> – LUSANO</title>
  <meta name="robots" content="index,follow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Primary Meta -->
  <meta name="description" content="<?php echo htmlspecialchars($desc); ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo htmlspecialchars($title); ?> – LUSANO">
  <meta property="og:description" content="<?php echo htmlspecialchars($desc); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($absImage); ?>">
  <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($absImage); ?>">
  <meta property="og:image:type" content="<?php echo htmlspecialchars($imgType); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($shareUrl); ?>">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($title); ?> – LUSANO">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($desc); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($absImage); ?>">

  <link rel="canonical" href="<?php echo htmlspecialchars($shareUrl); ?>">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;line-height:1.5;padding:2rem;color:#111}
    .card{max-width:640px;margin:10vh auto;border:1px solid #ddd;border-radius:10px;padding:1.5rem}
    .img{width:100%;max-height:320px;object-fit:contain;background:#fafafa;border:1px solid #eee;border-radius:8px}
    .muted{color:#666}
    a.btn{display:inline-block;margin-top:1rem;background:#111;color:#fff;padding:.6rem 1rem;border-radius:8px;text-decoration:none}
  </style>
  <!-- This page exists for crawlers to pick OG tags, then redirects users to the real product page. -->
</head>
<body>
  <div class="card">
    <img class="img" src="<?php echo htmlspecialchars($absImage); ?>" alt="<?php echo htmlspecialchars($title); ?>">
    <h1><?php echo htmlspecialchars($title); ?> – LUSANO</h1>
    <p class="muted"><?php echo htmlspecialchars($desc); ?></p>
    <p><a class="btn" href="<?php echo htmlspecialchars($target); ?>">Continue to product</a></p>
    <noscript><p class="muted">JavaScript is disabled. Click the button above to continue.</p></noscript>
  </div>
  <script>
    (function () {
      var ua = navigator.userAgent || '';
      var isBot = /bot|crawl|spider|facebookexternalhit|WhatsApp|Slack|Telegram|LinkedIn|Twitter|Discord/i.test(ua);
      if (!isBot) {
        setTimeout(function () {
          location.replace(<?php echo json_encode($target); ?>);
        }, 500);
      }
    })();
  </script>
</body>
</html>
