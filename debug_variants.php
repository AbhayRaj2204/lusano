<?php
require_once 'admin/includes/ColorVariantManager.php';

$manager = new ColorVariantManager();
$variants = $manager->getColorVariants();

echo "Total variants found: " . count($variants) . "\n";
echo "--------------------------------\n";

foreach ($variants as $v) {
    echo "Variant ID: " . $v['id'] . "\n";
    echo "Product Name: " . $v['product_name'] . "\n";
    echo "Color: " . $v['color_name'] . "\n";
    echo "Images: " . $v['image_1'] . "\n";
    echo "--------------------------------\n";
}

$testName = "HC-200 Door Closer";
echo "Testing match for: '$testName'\n";
$matched = array_filter($variants, function($v) use ($testName) {
    $vName = trim($v['product_name']);
    $qName = trim($testName);
    return stripos($vName, $qName) !== false || stripos($qName, $vName) !== false;
});

echo "Matches found: " . count($matched) . "\n";
?>
