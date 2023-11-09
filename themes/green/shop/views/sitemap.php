<?php echo'<?xml version="1.0" encoding="utf-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?php echo base_url();?></loc>
    <priority>1.0</priority>
    <lastmod><?php echo date('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
  </url>
  <?php foreach($products as $product) { ?>
  <url>
    <loc><?php echo base_url()."product/".$product->slug ?></loc>
    <priority>0.9</priority>
    <lastmod><?php echo date('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
  </url>
  <?php } ?>
</urlset>