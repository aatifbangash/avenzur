<?php echo'<?xml version="1.0" encoding="utf-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?php echo base_url();?></loc>
    <priority>1.0</priority>
    <lastmod><?php echo date('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
  </url>
  <?php
  if (!empty($categories)) {
      foreach ($categories as $category) {
        if(!empty($category)){
        ?>
        <url>
          <loc><?php echo base_url('category/' . $category->slug); ?></loc>
          <priority>0.8</priority>
          <lastmod><?php echo date('Y-m-d'); ?></lastmod>
          <changefreq>monthly</changefreq>
        </url>
        <?php
        }
      }
  }

  if (!empty($brands)) {
      foreach ($brands as $brand) {
        if(!empty($brand) && strpos($slug, '&') !== false){
        ?>
        <url>
          <loc><?php echo base_url('brand/' . $brand->slug); ?></loc>
          <priority>0.8</priority>
          <lastmod><?php echo date('Y-m-d'); ?></lastmod>
          <changefreq>monthly</changefreq>
        </url>
        <?php
        }
      }
  }
  ?>
  <?php foreach($products as $product) {
    if(!empty($product)){ ?>
  <url>
    <loc><?php echo base_url()."product/".$product->slug ?></loc>
    <priority>0.9</priority>
    <lastmod><?php echo date('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
  </url>
  <?php 
    }
  } ?>

  <?php 
  if (!empty($pages)) {
    foreach ($pages as $page) { 
      if(!empty($page)){
      ?>
      <url>
        <loc><?php echo base_url('page/' . $page->slug); ?></loc>
        <priority>0.8</priority>
        <?php
        if ($page->updated_at) {
          ?>
          <lastmod><?php echo date('Y-m-d', strtotime($page->updated_at)); ?></lastmod>
          <?php
        }
        ?>
        <changefreq>monthly</changefreq>
      </url>
      <?php
      }
    }
  }
  ?>
</urlset>