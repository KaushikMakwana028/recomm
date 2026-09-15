<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('security');
    }
    public function index()
    {
        $this->setPageTitle('Products');
        $this->loadView('products/index');
    }

    // List all products
    public function get_products()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $search      = trim($this->input->post('search', true));
        $category_id = trim($this->input->post('category_id', true));
        $page        = (int)$this->input->post('page', true) ?: 1;
        $limit       = 10;
        $offset      = ($page - 1) * $limit;

        // Build query
        $this->db->select('products.*, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');

        // Apply category filter
        if (!empty($category_id)) {
            $this->db->where('products.category_id', $category_id);
        }

        // Apply search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('products.name', $search);
            $this->db->or_like('products.sku', $search);
            $this->db->group_end();
        }

        // Total records for pagination
        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

        // Get paginated results
        $products = $this->db
            ->order_by('products.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();

        // Generate HTML
        $html       = $this->generate_products_html($products, $offset);
        $pagination = $this->generate_pagination($page, $total_pages);

        echo json_encode([
            'status'        => true,
            'html'          => $html,
            'pagination'    => $pagination,
            'total_records' => $total_records,
            'current_page'  => $page,
            'total_pages'   => $total_pages
        ]);
    }

    // Generate products table HTML
    private function generate_products_html($products, $offset = 0)
    {
        if (empty($products)) {
            return '
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No products found</p>
                        <a href="' . base_url('products/add') . '" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-2"></i>Add First Product
                        </a>
                    </td>
                </tr>
            ';
        }

        // Fetch variants for all loaded products
        $product_ids = array_map(function($p) { return (int)$p->id; }, $products);
        $variants_by_product = [];
        if (!empty($product_ids)) {
            $vars = $this->db->where_in('product_id', $product_ids)->order_by('id', 'ASC')->get('product_variants')->result();
            foreach ($vars as $v) {
                $variants_by_product[$v->product_id][] = $v;
            }
        }

        $html = '';
        foreach ($products as $index => $product) {

            // Serial number (descending)
            $serial = $offset + $index + 1;

            // Product image
            if (!empty($product->image)) {
                $image_html = '
                    <img src="' . base_url(htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8')) . '" 
                         alt="' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '" 
                         class="img-thumbnail" 
                         style="width: 60px; height: 60px; object-fit: cover;">
                ';
            } else {
                $image_html = '
                    <div style="width: 60px; height: 60px; background: #e9ecef; 
                                display: flex; align-items: center; 
                                justify-content: center; border-radius: 8px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                ';
            }

            // Category badge
            $category_html = !empty($product->category_name)
                ? '<span class="badge bg-info">' . htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8') . '</span>'
                : '<span class="text-muted">-</span>';

            // Price
            $price_html = '<strong>₹' . number_format($product->price, 2) . '</strong>';
            if (!empty($product->sale_price)) {
                $price_html .= '<br><small class="text-success">Sale: ₹' . number_format($product->sale_price, 2) . '</small>';
            }

            // Brand & Variants
            $brand_html = !empty($product->brand) ? '<small class="text-primary d-block fw-semibold"><i class="fas fa-tag me-1"></i>' . htmlspecialchars($product->brand, ENT_QUOTES, 'UTF-8') . '</small>' : '';
            $variants_html = '';
            if (!empty($variants_by_product[$product->id])) {
                $variants_html .= '<div class="mt-1 d-flex flex-wrap gap-1">';
                foreach ($variants_by_product[$product->id] as $v) {
                    $variants_html .= '<span class="badge bg-light text-dark border" style="font-size: 0.72rem;">' . htmlspecialchars($v->variant_name, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                $variants_html .= '</div>';
            }

            // Status toggle
            $checked = $product->is_active == 1 ? 'checked' : '';

            $html .= '
                <tr>
                    <td><strong>' . $serial . '</strong></td>
                    <td>' . $image_html . '</td>
                    <td>
                        <strong>' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '</strong>
                        ' . $brand_html . '
                        ' . $variants_html . '
                        <small class="text-muted d-block mt-1">
                            SKU: ' . htmlspecialchars($product->sku ?: 'N/A', ENT_QUOTES, 'UTF-8') . '
                        </small>
                    </td>
                    <td>' . $category_html . '</td>
                    <td>' . $price_html . '</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle" 
                                   type="checkbox" 
                                   role="switch"
                                   data-id="' . (int)$product->id . '" 
                                   aria-label="Toggle status"
                                   ' . $checked . '>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="' . base_url('products/edit/' . (int)$product->id) . '" 
                               class="rc-btn-icon edit" 
                               title="Edit"
                               aria-label="Edit ' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    class="rc-btn-icon delete btn-delete"
                                    data-id="' . (int)$product->id . '"
                                    data-name="' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '"
                                    title="Delete"
                                    aria-label="Delete ' . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            ';
        }

        return $html;
    }

    // Generate pagination HTML
    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html  = '<nav aria-label="Products pagination">';
        $html .= '<ul class="pagination pagination-sm justify-content-center mb-0">';

        // Previous button
        if ($current_page > 1) {
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . ($current_page - 1) . '">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            ';
        }

        // Calculate 3 page buttons
        $start_page = max(1, $current_page - 1);
        $end_page   = min($total_pages, $start_page + 2);

        // Adjust if near end
        if ($end_page - $start_page < 2) {
            $start_page = max(1, $end_page - 2);
        }

        // Page buttons
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $current_page ? 'active' : '';
            $html .= '
                <li class="page-item ' . $active . '">
                    <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                </li>
            ';
        }

        // Last page with dots
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $html .= '
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                ';
            }
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . $total_pages . '">' . $total_pages . '</a>
                </li>
            ';
        }

        // Next button
        if ($current_page < $total_pages) {
            $html .= '
                <li class="page-item">
                    <a class="page-link" href="#" data-page="' . ($current_page + 1) . '">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            ';
        }

        $html .= '</ul></nav>';

        return $html;
    }


    public function add()
    {
        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run()) {
                $slug = $this->generateSlug($this->post('name'));

                // Check if slug exists
                if ($this->gm->exists('products', ['slug' => $slug])) {
                    $slug = $slug . '-' . time();
                }

                // Handle main image upload
                $image = '';
                if (!empty($_FILES['image']['name'])) {
                    $image = $this->uploadProductImage('image');
                    if (!$image) {
                        $this->setMessage('danger', $this->upload->display_errors());
                        redirect('products/add');
                        return;
                    }
                }

                // Handle gallery images
                $gallery = [];
                if (!empty($_FILES['gallery']['name'][0])) {
                    $gallery = $this->uploadGalleryImages();
                }

                $data = [
                    'category_id' => $this->post('category_id'),
                    'name' => $this->post('name'),
                    'brand' => $this->post('brand') ?: NULL,
                    'slug' => $slug,
                    'description' => $this->post('description'),
                    'price' => $this->post('price'),
                    'sale_price' => $this->post('sale_price') ?: NULL,
                    'sku' => $this->post('sku'),
                    'image' => $image,
                    'gallery' => !empty($gallery) ? json_encode($gallery) : NULL,
                    'is_active' => $this->post('is_active') ? 1 : 0,
                    'created_by' => 'admin',
                    'created_on' => date('Y-m-d H:i:s')
                ];

                if ($this->gm->insert('products', $data)) {
                    $product_id = $this->db->insert_id();

                    // Save variants / pack sizes
                    $variant_names = (array) $this->input->post('variant_names');
                    $variant_skus = (array) $this->input->post('variant_skus');
                    $variant_mrps = (array) $this->input->post('variant_mrps');
                    $variant_prices = (array) $this->input->post('variant_prices');

                    foreach ($variant_names as $idx => $vname) {
                        $vname = trim($vname);
                        if (!empty($vname)) {
                            $this->db->insert('product_variants', [
                                'product_id'   => $product_id,
                                'variant_name' => $vname,
                                'sku'          => trim($variant_skus[$idx] ?? '') ?: null,
                                'mrp'          => isset($variant_mrps[$idx]) && is_numeric($variant_mrps[$idx]) ? floatval($variant_mrps[$idx]) : null,
                                'price'        => isset($variant_prices[$idx]) && is_numeric($variant_prices[$idx]) ? floatval($variant_prices[$idx]) : null,
                                'is_active'    => 1,
                                'created_at'   => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    $this->setMessage('success', 'Product and variants added successfully');
                    redirect('products');
                } else {
                    $this->setMessage('danger', 'Failed to add product');
                }
            }
        }

        $data['categories'] = $this->gm->getAll('categories', ['is_active' => 1], '*', 'name ASC');

        $this->setPageTitle('Add Product');
        $this->loadView('products/add', $data);
    }


    public function edit($id)
    {
        $data['product'] = $this->gm->getById('products', $id);

        if (!$data['product']) {
            $this->setMessage('danger', 'Product not found');
            redirect('products');
        }

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Product Name', 'required|trim');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');

            if ($this->form_validation->run()) {
                $update_data = [
                    'category_id' => $this->post('category_id'),
                    'name' => $this->post('name'),
                    'brand' => $this->post('brand') ?: NULL,
                    'description' => $this->post('description'),
                    'price' => $this->post('price'),
                    'sale_price' => $this->post('sale_price') ?: NULL,
                    'sku' => $this->post('sku'),
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                // Handle main image upload
                if (!empty($_FILES['image']['name'])) {
                    $image = $this->uploadProductImage('image');
                    if ($image) {
                        // Delete old image
                        if ($data['product']->image && file_exists($data['product']->image)) {
                            unlink($data['product']->image);
                        }
                        $update_data['image'] = $image;
                    }
                }

                // Handle gallery images
                if (!empty($_FILES['gallery']['name'][0])) {
                    $gallery = $this->uploadGalleryImages();
                    if (!empty($gallery)) {
                        // Delete old gallery images
                        if ($data['product']->gallery) {
                            $old_gallery = json_decode($data['product']->gallery, true);
                            foreach ($old_gallery as $img) {
                                if (file_exists($img)) {
                                    unlink($img);
                                }
                            }
                        }
                        $update_data['gallery'] = json_encode($gallery);
                    }
                }

                if ($this->gm->update('products', $update_data, ['id' => $id])) {
                    // Sync variants
                    $this->db->where('product_id', $id)->delete('product_variants');

                    $variant_names = (array) $this->input->post('variant_names');
                    $variant_skus = (array) $this->input->post('variant_skus');
                    $variant_mrps = (array) $this->input->post('variant_mrps');
                    $variant_prices = (array) $this->input->post('variant_prices');

                    foreach ($variant_names as $idx => $vname) {
                        $vname = trim($vname);
                        if (!empty($vname)) {
                            $this->db->insert('product_variants', [
                                'product_id'   => $id,
                                'variant_name' => $vname,
                                'sku'          => trim($variant_skus[$idx] ?? '') ?: null,
                                'mrp'          => isset($variant_mrps[$idx]) && is_numeric($variant_mrps[$idx]) ? floatval($variant_mrps[$idx]) : null,
                                'price'        => isset($variant_prices[$idx]) && is_numeric($variant_prices[$idx]) ? floatval($variant_prices[$idx]) : null,
                                'is_active'    => 1,
                                'created_at'   => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    $this->setMessage('success', 'Product and variants updated successfully');
                    redirect('products');
                } else {
                    $this->setMessage('danger', 'Failed to update product');
                }
            }
        }

        $data['categories'] = $this->gm->getAll('categories', ['is_active' => 1], '*', 'name ASC');
        $data['gallery_images'] = $data['product']->gallery ? json_decode($data['product']->gallery, true) : [];
        $data['variants'] = $this->db->get_where('product_variants', ['product_id' => $id])->result();

        $this->setPageTitle('Edit Product');
        $this->loadView('products/edit', $data);
    }

    // Delete product
    public function delete($id)
    {
        $product = $this->gm->getById('products', $id);

        if ($product) {
            // Check if any vendor using this product
            $vendor_count = $this->gm->countRows('vendor_products', ['product_id' => $id]);

            if ($vendor_count > 0) {
                $this->setMessage('warning', 'Cannot delete! This product is being used by ' . $vendor_count . ' vendor(s)');
                redirect('products');
                return;
            }

            // Delete images
            if ($product->image && file_exists($product->image)) {
                unlink($product->image);
            }

            if ($product->gallery) {
                $gallery = json_decode($product->gallery, true);
                foreach ($gallery as $img) {
                    if (file_exists($img)) {
                        unlink($img);
                    }
                }
            }

            // Delete associated variants
            $this->db->where('product_id', $id)->delete('product_variants');

            if ($this->gm->delete('products', ['id' => $id])) {
                $this->setMessage('success', 'Product deleted successfully');
            } else {
                $this->setMessage('danger', 'Failed to delete product');
            }
        } else {
            $this->setMessage('danger', 'Product not found');
        }

        redirect('products');
    }

    // Toggle status
    public function toggle_status()
    {
        if ($this->isAjax()) {
            $id = $this->post('id');
            $status = $this->post('status');

            if ($this->gm->update('products', ['is_active' => $status], ['id' => $id])) {
                $this->jsonResponse(true, 'Status updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update status');
            }
        }
    }

    // Upload product image
    private function uploadProductImage($field_name)
    {
        $upload_path = './assets/uploads/products/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return 'assets/uploads/products/' . $upload_data['file_name'];
        }

        return false;
    }

    // Upload gallery images
    private function uploadGalleryImages()
    {
        $upload_path = './assets/uploads/products/gallery/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $gallery = [];
        $files = $_FILES['gallery'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (!empty($files['name'][$i])) {
                $_FILES['file']['name'] = $files['name'][$i];
                $_FILES['file']['type'] = $files['type'][$i];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file']['error'] = $files['error'][$i];
                $_FILES['file']['size'] = $files['size'][$i];

                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);

                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $gallery[] = 'assets/uploads/products/gallery/' . $upload_data['file_name'];
                }
            }
        }

        return $gallery;
    }

    // Search products (AJAX for vendors)
    public function search()
    {
        if ($this->isAjax()) {
            $keyword = $this->post('keyword');

            $products = $this->gm->search('products', ['name', 'sku'], $keyword, ['is_active' => 1]);

            $result = [];
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'image' => $product->image ? base_url($product->image) : ''
                ];
            }

            $this->jsonResponse(true, 'Products found', $result);
        }
    }


    // ============================
    // BULK ADD PRODUCTS
    // ============================

    // Download CSV template
    public function download_template()
    {
        $filename = 'products_bulk_upload_template.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Product Name',
            'Category',
            'Regular Price',
            'Sale Price',
            'Main Image (HTTPS URL)',
            'SKU (Optional)',
            'Description (Optional)',
            'Gallery Images (Optional, separate multiple URLs with |)'
        ]);

        fputcsv($output, [
            'Sprite 2 L',
            'Beverages',
            '100',
            '90',
            'https://example.com/images/sprite.jpg',
            'SPR-001',
            'Chilled lemon-lime soft drink',
            'https://example.com/images/sprite2.jpg|https://example.com/images/sprite3.jpg'
        ]);

        fclose($output);
        exit;
    }

    // Validate + insert products from an uploaded CSV/XLSX/XLS file
    public function bulk_upload()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        set_time_limit(600);
        ini_set('memory_limit', '512M');

        if (empty($_FILES['bulk_file']['name'])) {
            echo json_encode(['status' => false, 'message' => 'Please select a file to upload']);
            return;
        }

        $file    = $_FILES['bulk_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['csv', 'xlsx', 'xls'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => false, 'message' => 'Invalid file type. Only CSV, XLSX, XLS files are allowed']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => false, 'message' => 'File size too large. Max 5MB allowed']);
            return;
        }

        try {
            $rows = $this->parse_bulk_file($file['tmp_name'], $ext);
        } catch (\Exception $e) {
            echo json_encode(['status' => false, 'message' => 'Unable to read file: ' . $e->getMessage()]);
            return;
        }

        if (empty($rows)) {
            echo json_encode(['status' => false, 'message' => 'No data found in file']);
            return;
        }

        // Category name => id lookup
        $categories   = $this->gm->getAll('categories', ['is_active' => 1], 'id, name');
        $category_map = [];
        foreach ($categories as $cat) {
            $category_map[strtolower(trim($cat->name))] = $cat->id;
        }

        // 1. Collect and deduplicate all remote URLs to validate in parallel
        $urls_to_validate = [];
        foreach ($rows as $index => $row) {
            $name        = trim($row['product_name'] ?? '');
            $category    = trim($row['category'] ?? '');
            $price       = trim($row['regular_price'] ?? '');
            $sale_price  = trim($row['sale_price'] ?? '');
            $image_url   = trim($row['main_image'] ?? '');
            $gallery_raw = trim($row['gallery_images'] ?? '');

            // skip fully blank lines
            if ($name === '' && $category === '' && $price === '' && $sale_price === '' && $image_url === '') {
                continue;
            }

            if ($image_url !== '') {
                $urls_to_validate[] = $image_url;
            }
            if ($gallery_raw !== '') {
                $gallery_urls = array_filter(array_map('trim', explode('|', $gallery_raw)));
                foreach ($gallery_urls as $g_url) {
                    $urls_to_validate[] = $g_url;
                }
            }
        }
        $urls_to_validate = array_unique($urls_to_validate);

        // 2. Perform parallel validation check on all remote URLs
        $validation_results = $this->validate_remote_images_parallel($urls_to_validate);

        // 3. Process records and build error report using pre-fetched validation results
        $errors         = [];
        $valid_products = [];

        foreach ($rows as $index => $row) {
            $row_no = $index + 2; // header row = 1

            $name        = trim($row['product_name'] ?? '');
            $category    = trim($row['category'] ?? '');
            $price       = trim($row['regular_price'] ?? '');
            $sale_price  = trim($row['sale_price'] ?? '');
            $image_url   = trim($row['main_image'] ?? '');
            $sku         = trim($row['sku'] ?? '');
            $description = trim($row['description'] ?? '');
            $gallery_raw = trim($row['gallery_images'] ?? '');

            // skip fully blank lines
            if ($name === '' && $category === '' && $price === '' && $sale_price === '' && $image_url === '') {
                continue;
            }

            $row_errors = [];

            if ($name === '') {
                $row_errors[] = 'Product Name is missing';
            }

            $category_id = null;
            if ($category === '') {
                $row_errors[] = 'Category is missing';
            } elseif (!isset($category_map[strtolower($category)])) {
                $row_errors[] = 'Category "' . $category . '" does not exist';
            } else {
                $category_id = $category_map[strtolower($category)];
            }

            if ($price === '') {
                $row_errors[] = 'Regular Price is missing';
            } elseif (!is_numeric($price) || $price <= 0) {
                $row_errors[] = 'Regular Price is invalid';
            }

            if ($sale_price === '') {
                $row_errors[] = 'Sale Price is missing';
            } elseif (!is_numeric($sale_price) || $sale_price <= 0) {
                $row_errors[] = 'Sale Price is invalid';
            } elseif (is_numeric($price) && $sale_price >= $price) {
                $row_errors[] = 'Sale Price must be less than Regular Price';
            }

            if ($image_url === '') {
                $row_errors[] = 'Main Image is missing';
            } elseif (stripos($image_url, 'https://') !== 0) {
                $row_errors[] = 'Main Image must be a valid HTTPS URL';
            } else {
                $check = $validation_results[$image_url] ?? 'Could not validate image';
                if ($check !== true) {
                    $row_errors[] = 'Main Image: ' . $check;
                }
            }

            $gallery_urls = [];
            if ($gallery_raw !== '') {
                $gallery_urls = array_filter(array_map('trim', explode('|', $gallery_raw)));
                foreach ($gallery_urls as $g_url) {
                    if (stripos($g_url, 'https://') !== 0) {
                        $row_errors[] = 'Gallery Image URL must be HTTPS: ' . $g_url;
                        continue;
                    }
                    $g_check = $validation_results[$g_url] ?? 'Could not validate image';
                    if ($g_check !== true) {
                        $row_errors[] = 'Gallery Image (' . $g_url . '): ' . $g_check;
                    }
                }
            }

            if (!empty($row_errors)) {
                $errors[] = ['row' => $row_no, 'product' => $name ?: '(Unnamed)', 'errors' => $row_errors];
                continue;
            }

            $valid_products[] = [
                'category_id'  => $category_id,
                'name'         => $name,
                'price'        => $price,
                'sale_price'   => $sale_price,
                'sku'          => $sku,
                'description'  => $description,
                'image_url'    => $image_url,
                'gallery_urls' => $gallery_urls
            ];
        }

        // ANY error anywhere -> add nothing
        if (!empty($errors)) {
            echo json_encode([
                'status'  => false,
                'message' => 'Fix the errors below and re-upload. No products were added.',
                'errors'  => $errors
            ]);
            return;
        }

        if (empty($valid_products)) {
            echo json_encode(['status' => false, 'message' => 'No valid product rows found in file']);
            return;
        }

        // 4. Collect and deduplicate image download tasks
        $url_to_filename = [];
        $distinct_downloads = [];

        foreach ($valid_products as $idx => $p) {
            $urls = array_merge([$p['image_url']], $p['gallery_urls']);
            foreach ($urls as $url) {
                if ($url === '') continue;
                if (!isset($url_to_filename[$url])) {
                    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $ext = 'jpg';
                    }
                    $filename = md5(uniqid('', true)) . '.' . $ext;

                    $is_gallery = in_array($url, $p['gallery_urls']);
                    $dest = $is_gallery ? './assets/uploads/products/gallery/' . $filename : './assets/uploads/products/' . $filename;
                    $rel = $is_gallery ? 'assets/uploads/products/gallery/' . $filename : 'assets/uploads/products/' . $filename;

                    $url_to_filename[$url] = [
                        'destination' => $dest,
                        'relative_path' => $rel
                    ];

                    $distinct_downloads[] = [
                        'url' => $url,
                        'destination' => $dest,
                        'relative_path' => $rel
                    ];
                }
            }
        }

        // 5. Download distinct images in parallel
        $download_results = $this->download_remote_images_parallel($distinct_downloads);

        $downloaded_files = [];
        $failed_p_name = null;

        // Verify and map paths to products
        foreach ($valid_products as &$p) {
            $main_url = $p['image_url'];
            $main_res = $download_results[$main_url] ?? false;

            if ($main_res === false) {
                $failed_p_name = $p['name'];
                break;
            }

            $p['local_image'] = $main_res;

            $p['local_gallery'] = [];
            foreach ($p['gallery_urls'] as $g_url) {
                $g_res = $download_results[$g_url] ?? false;
                if ($g_res !== false) {
                    $p['local_gallery'][] = $g_res;
                }
            }
        }
        unset($p);

        // Collect downloaded files on disk for cleanup
        foreach ($download_results as $url => $res) {
            if ($res !== false) {
                $downloaded_files[] = $url_to_filename[$url]['destination'];
            }
        }

        if ($failed_p_name !== null) {
            $this->cleanup_files($downloaded_files);
            echo json_encode(['status' => false, 'message' => 'Failed to download image for "' . $failed_p_name . '". No products were added.']);
            return;
        }

        // 6. DB Transaction insert
        $this->db->trans_start();

        $inserted = 0;
        foreach ($valid_products as $p) {
            $slug = $this->generateSlug($p['name']);
            if ($this->gm->exists('products', ['slug' => $slug])) {
                $slug = $slug . '-' . time() . rand(10, 99);
            }

            $this->gm->insert('products', [
                'category_id' => $p['category_id'],
                'name'        => $p['name'],
                'slug'        => $slug,
                'description' => $p['description'] ?: NULL,
                'price'       => $p['price'],
                'sale_price'  => $p['sale_price'],
                'sku'         => $p['sku'] ?: NULL,
                'image'       => $p['local_image'],
                'gallery'     => !empty($p['local_gallery']) ? json_encode($p['local_gallery']) : NULL,
                'is_active'   => 1,
                'created_by'  => 'admin',
                'created_on'  => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->cleanup_files($downloaded_files);
            echo json_encode(['status' => false, 'message' => 'Database error. No products were added.']);
            return;
        }

        echo json_encode(['status' => true, 'message' => $inserted . ' product(s) added successfully']);
    }

    // Parse CSV / XLSX / XLS into an array of associative rows
    private function parse_bulk_file($tmp_path, $ext)
    {
        $rows = [];

        $normalize = function ($h) {
            $h = preg_replace('/\s*\(.*?\)\s*/', '', (string)$h);
            return str_replace(' ', '_', strtolower(trim($h)));
        };

        if ($ext === 'csv') {
            if (($handle = fopen($tmp_path, 'r')) !== false) {
                $header = array_map($normalize, fgetcsv($handle));
                while (($data = fgetcsv($handle)) !== false) {
                    if (count(array_filter($data, function ($v) {
                        return trim((string)$v) !== '';
                    })) === 0) {
                        continue;
                    }
                    $rows[] = array_combine($header, array_pad($data, count($header), ''));
                }
                fclose($handle);
            }
        } else {
            // Load via PhpSpreadsheet / PHPExcel
            require_once APPPATH . '../vendor/autoload.class.php';
            if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                // Try to load PHPExcel if PhpSpreadsheet doesn't exist
                $reader = PHPExcel_IOFactory::createReaderForFile($tmp_path);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmp_path);
            }
            $sheet = $reader->load($tmp_path)->getActiveSheet();
            $data  = $sheet->toArray(null, true, true, false);

            $header = array_map($normalize, array_shift($data));
            foreach ($data as $line) {
                if (count(array_filter($line, function ($v) {
                    return trim((string)$v) !== '';
                })) === 0) {
                    continue;
                }
                $rows[] = array_combine($header, array_pad($line, count($header), ''));
            }
        }

        return $rows;
    }

    // Parallel remote image validation (HEAD requests using curl_multi)
    private function validate_remote_images_parallel($urls)
    {
        $results = [];
        if (empty($urls)) {
            return $results;
        }

        $chunks = array_chunk($urls, 100);
        foreach ($chunks as $chunk) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($chunk as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);

                curl_multi_add_handle($mh, $ch);
                $handles[$url] = $ch;
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                } else {
                    usleep(100);
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
            }

            foreach ($handles as $url => $ch) {
                if (curl_errno($ch)) {
                    $err = curl_error($ch);
                    $results[$url] = 'Could not reach image URL (' . $err . ')';
                } else {
                    $http_code      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $content_type   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

                    if ($http_code != 200) {
                        $results[$url] = 'Image URL not reachable (HTTP ' . $http_code . ')';
                    } elseif (!$content_type || stripos($content_type, 'image/') !== 0) {
                        $results[$url] = 'URL does not point to a valid image';
                    } elseif ($content_length > 0 && $content_length > 1048576) {
                        $results[$url] = 'Image size exceeds 1MB';
                    } else {
                        $results[$url] = true;
                    }
                }
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }

        return $results;
    }

    // Parallel remote image downloading (GET requests using curl_multi)
    private function download_remote_images_parallel($downloads)
    {
        $results = [];
        if (empty($downloads)) {
            return $results;
        }

        $chunks = array_chunk($downloads, 50);
        foreach ($chunks as $chunk) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($chunk as $item) {
                $url  = $item['url'];
                $dest = $item['destination'];
                $rel  = $item['relative_path'];

                $dir = dirname($dest);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $fp = fopen($dest, 'w');
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_multi_add_handle($mh, $ch);
                $handles[$url] = [
                    'ch'   => $ch,
                    'dest' => $dest,
                    'rel'  => $rel,
                    'fp'   => $fp
                ];
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                } else {
                    usleep(100);
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
            }

            foreach ($handles as $url => $info) {
                $ch   = $info['ch'];
                $dest = $info['dest'];
                $rel  = $info['rel'];
                $fp   = $info['fp'];

                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $success   = !curl_errno($ch);

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                fclose($fp);

                if (!$success || $http_code != 200 || !file_exists($dest) || filesize($dest) === 0 || filesize($dest) > 1048576) {
                    if (file_exists($dest)) {
                        unlink($dest);
                    }
                    $results[$url] = false;
                } else {
                    $results[$url] = $rel;
                }
            }
            curl_multi_close($mh);
        }

        return $results;
    }

    private function cleanup_files($paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) unlink($path);
        }
    }
}
