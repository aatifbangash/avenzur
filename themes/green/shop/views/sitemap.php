<?php echo'<?xml version="1.0" encoding="UTF-8" ?>' ?>
<?php echo'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'; ?>
<?php echo'<url>'; ?>
        <?php echo'<loc>'.base_url().'</loc>'; ?>
        <?php echo'<priority>1.0</priority>'; ?>
        <?php echo'<lastmod>'.date('Y-m-d').'</lastmod>'; ?>
        <?php echo'<changefreq>daily</changefreq>'; ?>
<?php echo'</url>'; ?>
 
    <?php foreach($products as $product) { ?>
      <?php echo'<url>'; ?>
      <?php echo'<loc>'.base_url()."product/".$product->slug.'</loc>'; ?>
      <?php echo'<priority>0.9</priority>'; ?>
      <?php echo'<lastmod>'.date('Y-m-d').'</lastmod>'; ?>
      <?php echo'<changefreq>daily</changefreq>'; ?>
      <?php echo'</url>'; ?>
    <?php } ?>
<?php echo '</urlset>'; ?>