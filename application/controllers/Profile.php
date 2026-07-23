<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Profile view and update
     */
    public function index()
    {
        $this->setPageTitle('My Profile');
        $user = $this->admin_data;

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('name', 'Full Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email Address', 'valid_email|trim');
            $this->form_validation->set_rules('mobile', 'Mobile Number', 'required|numeric|exact_length[10]');

            if ($this->form_validation->run()) {
                $mobile = $this->post('mobile');
                
                // Check if mobile already exists for another user
                $existing_user = $this->gm->getOne('users', [
                    'mobile' => $mobile,
                    'id !=' => $user->id
                ]);

                if ($existing_user) {
                    $this->setMessage('danger', 'The mobile number is already registered to another account.');
                } else {
                    $update_data = [
                        'name' => $this->post('name'),
                        'email' => $this->post('email') ?: NULL,
                        'mobile' => $mobile
                    ];

                    // Handle Profile Photo Upload
                    if (!empty($_FILES['profile_image']['name'])) {
                        $profile_image = $this->uploadProfileImage('profile_image');
                        if ($profile_image) {
                            // Unlink old profile photo if it exists and is not default
                            if ($user->profile_image && file_exists($user->profile_image)) {
                                @unlink($user->profile_image);
                            }
                            $update_data['profile_image'] = $profile_image;
                        }
                    }

                    if ($this->gm->update('users', $update_data, ['id' => $user->id])) {
                        // Refresh active session data
                        $this->session->set_userdata('admin_name', $update_data['name']);
                        $this->setMessage('success', 'Profile updated successfully.');
                        redirect('settings/profile');
                    } else {
                        $this->setMessage('danger', 'Failed to update profile details.');
                    }
                }
            }
        }

        $data['user'] = $this->gm->getById('users', $user->id); // reload fresh data
        $this->loadView('profile/index', $data);
    }



    /**
     * Profile photo upload handler
     */
    private function uploadProfileImage($field_name)
    {
        $upload_path = './assets/uploads/users/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048; // 2MB max
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            return 'assets/uploads/users/' . $this->upload->data()['file_name'];
        }
        return false;
    }
}
