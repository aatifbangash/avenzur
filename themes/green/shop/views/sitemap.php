<xml version="1.0" encoding="UTF-8">
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    <url>
        <loc><?php echo base_url();?></loc>
        <priority>1.0</priority>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
    </url>
 
 
    <!-- Sitemap -->
    <?php foreach($products as $product) { ?>
    <url>
        <loc><?php echo base_url()."product/".$product->slug ?></loc>
        <priority>0.9</priority>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
    </url>
    <?php } ?>
</urlset>
</xml>