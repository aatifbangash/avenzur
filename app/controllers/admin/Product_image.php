<?php defined('BASEPATH') or exit('No direct script access allowed');

class Product_image extends MY_Controller {

    public function remove_image($id) {

        // Load the product model
        // $this->load->model('Product_model');
        $this->load->admin_model('products_model');
        // Get the product by id
        $product = $this->products_model->getProductByID($id);
        
        if ($product) {
            // Path to the image file
            $image_path = './assets/uploads/'.$product->image;
            
            // Delete the image file from the server
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            
            // Update the database to remove the image reference
            $this->products_model->remove_image($id);
            // Set a success message
            echo json_encode(['status' => 'success', 'message' => 'Image removed successfully.']);
        } else {
            // Set an error message
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        }
    }
}