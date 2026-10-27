<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hero_section extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hero section management page
     */
    public function index()
    {
        $this->setPageTitle('Hero Banner & Slider');

        // Retrieve hero text content
        $content = $this->gm->getOne('hero_section', ['id' => 1]);
        if (!$content) {
            $default_data = [
                'id'          => 1,
                'title'       => 'Delicious Food & Beverages',
                'subtitle'    => 'Delivered to Your Door',
                'description' => 'Discover premium quality food and beverages. Fresh, organic, and delivered with care.',
                'btn_text'    => 'Shop Now',
                'btn_link'    => '/products',
                'btn2_text'   => 'Explore Products',
                'btn2_link'   => '#featured',
    
            ];
            $this->gm->insert('hero_section', $default_data);
            $content = (object)$default_data;
        }

        // Retrieve hero slider images
        $sliders = $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'DESC')
            ->get('hero_sliders')
            ->result();

        $this->loadView('hero_section/index', [
            'content' => $content,
            'sliders' => $sliders
        ]);
    }

    /**
     * Update Hero Content (Title, Subtitle, Description, Buttons, Stats)
     */
    public function update_content()
    {
        if ($this->input->method() !== 'post') {
            redirect('hero-section');
        }

        $this->form_validation->set_rules('title', 'Main Title', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('subtitle', 'Highlight Subtitle', 'trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('btn_text', 'Primary Button Text', 'trim|max_length[100]');
        $this->form_validation->set_rules('btn_link', 'Primary Button Link', 'trim|max_length[255]');
        $this->form_validation->set_rules('btn2_text', 'Secondary Button Text', 'trim|max_length[100]');
        $this->form_validation->set_rules('btn2_link', 'Secondary Button Link', 'trim|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->setMessage('danger', validation_errors());
            redirect('hero-section');
            return;
        }

        $data = [
            'title'       => $this->post('title'),
            'subtitle'    => $this->post('subtitle'),
            'description' => $this->post('description'),
            'btn_text'    => $this->post('btn_text') ?: 'Shop Now',
            'btn_link'    => $this->post('btn_link') ?: '/products',
            'btn2_text'   => $this->post('btn2_text') ?: 'Explore Products',
            'btn2_link'   => $this->post('btn2_link') ?: '#featured',

            'updated_at'  => date('Y-m-d H:i:s')
        ];

        $exists = $this->gm->getOne('hero_section', ['id' => 1]);
        if ($exists) {
            $this->gm->update('hero_section', $data, ['id' => 1]);
        } else {
            $data['id'] = 1;
            $this->gm->insert('hero_section', $data);
        }

        $this->setMessage('success', 'Hero content updated successfully!');
        redirect('hero-section');
    }

    /**
     * Add new slider image
     */
    public function add_slide()
    {
        if ($this->input->method() !== 'post') {
            redirect('hero-section');
        }

        if (empty($_FILES['image']['name'])) {
            $this->setMessage('danger', 'Please choose an image file to upload.');
            redirect('hero-section');
            return;
        }

        $upload_path = './assets/uploads/hero/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|webp|gif';
        $config['max_size']      = 5120; // 5MB
        $config['encrypt_name']  = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('image')) {
            $this->setMessage('danger', $this->upload->display_errors('', ''));
            redirect('hero-section');
            return;
        }

        $upload_data = $this->upload->data();
        $image_path  = 'assets/uploads/hero/' . $upload_data['file_name'];

        $title      = trim($this->post('title') ?? '');
        $sort_order = (int)$this->post('sort_order');
        $is_active  = $this->post('is_active') ? 1 : 0;

        $slide_data = [
            'title'      => $title,
            'image'      => $image_path,
            'sort_order' => $sort_order,
            'is_active'  => $is_active,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->gm->insert('hero_sliders', $slide_data);

        $this->setMessage('success', 'Slider image added successfully!');
        redirect('hero-section');
    }

    /**
     * Edit slide details (and optional image replacement)
     */
    public function edit_slide($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('hero-section');
        }

        $slide = $this->gm->getById('hero_sliders', $id);
        if (!$slide) {
            $this->setMessage('danger', 'Slide not found.');
            redirect('hero-section');
            return;
        }

        $title      = trim($this->post('title') ?? '');
        $sort_order = (int)$this->post('sort_order');
        $is_active  = $this->post('is_active') ? 1 : 0;

        $update_data = [
            'title'      => $title,
            'sort_order' => $sort_order,
            'is_active'  => $is_active,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if new image was provided
        if (!empty($_FILES['image']['name'])) {
            $upload_path = './assets/uploads/hero/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|webp|gif';
            $config['max_size']      = 5120;
            $config['encrypt_name']  = TRUE;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                // Delete old local image file
                if (!empty($slide->image) && !filter_var($slide->image, FILTER_VALIDATE_URL) && file_exists($slide->image)) {
                    @unlink($slide->image);
                }

                $upload_data = $this->upload->data();
                $update_data['image'] = 'assets/uploads/hero/' . $upload_data['file_name'];
            } else {
                $this->setMessage('danger', $this->upload->display_errors('', ''));
                redirect('hero-section');
                return;
            }
        }

        $this->gm->update('hero_sliders', $update_data, ['id' => $id]);
        $this->setMessage('success', 'Slide updated successfully!');
        redirect('hero-section');
    }

    /**
     * Delete slide
     */
    public function delete_slide($id)
    {
        $slide = $this->gm->getById('hero_sliders', $id);
        if ($slide) {
            if (!empty($slide->image) && !filter_var($slide->image, FILTER_VALIDATE_URL) && file_exists($slide->image)) {
                @unlink($slide->image);
            }
            $this->gm->delete('hero_sliders', ['id' => $id]);
            $this->setMessage('success', 'Slide image deleted successfully.');
        } else {
            $this->setMessage('danger', 'Slide image not found.');
        }

        redirect('hero-section');
    }

    /**
     * Toggle slide active/inactive status (AJAX)
     */
    public function toggle_status()
    {
        if ($this->isAjax()) {
            $id = (int)$this->post('id');
            $status = (int)$this->post('status') ? 1 : 0;

            if ($this->gm->update('hero_sliders', ['is_active' => $status], ['id' => $id])) {
                $this->jsonResponse(true, 'Slide status updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update slide status');
            }
        } else {
            show_404();
        }
    }
}
