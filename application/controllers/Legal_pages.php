<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legal_pages Controller
 *
 * Admin management for Terms & Conditions, Privacy Policy, and Refund Policy.
 * Provides card grid dashboard, two-pane editor with autosaving, draft/publish separation,
 * and policy-specific guidance.
 */
class Legal_pages extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('legal');
    }

    /**
     * Dashboard view showing policy card grid
     */
    public function index()
    {
        $this->setPageTitle('Legal Pages Manager');

        $slugs = get_all_legal_page_slugs();
        $pages = [];

        foreach ($slugs as $slug) {
            $pageData = resolve_legal_page($slug);
            $plainText = strip_tags($pageData['content']);
            $wordCount = str_word_count($plainText);
            $pageData['word_count'] = $wordCount;
            $pageData['read_time_mins'] = max(1, (int)ceil($wordCount / 200));
            $pageData['guidance'] = get_legal_page_guidance($slug);
            $pages[] = $pageData;
        }

        $data['pages'] = $pages;
        $this->loadView('legal_pages/index', $data);
    }

    /**
     * Two-pane editor view for a specific legal policy
     *
     * @param string $slug
     */
    public function edit($slug = '')
    {
        $slug = strtolower(trim($slug));
        $validSlugs = get_all_legal_page_slugs();

        if (!in_array($slug, $validSlugs)) {
            $this->setMessage('danger', 'Invalid policy slug specified.');
            redirect('legal-pages');
            return;
        }

        $page = resolve_legal_page($slug);
        $default = get_default_legal_page($slug);
        $guidance = get_legal_page_guidance($slug);

        $this->setPageTitle('Edit ' . $page['title']);

        // Determine what content should be loaded initially into the editor:
        // Priority 1: Unsaved or saved draft_content if present
        // Priority 2: Published content if customized
        // Priority 3: Built-in shipped default content
        $editorContent = $default['content'];
        if ($page['has_draft'] && $page['draft_content'] !== null && trim($page['draft_content']) !== '') {
            $editorContent = $page['draft_content'];
        } elseif (!empty($page['raw_published'])) {
            $editorContent = $page['raw_published'];
        }

        $data['page'] = $page;
        $data['default'] = $default;
        $data['guidance'] = $guidance;
        $data['editor_content'] = $editorContent;
        $data['slug'] = $slug;

        $this->loadView('legal_pages/edit', $data);
    }

    /**
     * AJAX endpoint to save draft without affecting live published content
     *
     * @param string $slug
     */
    public function save_draft($slug = '')
    {
        if (!$this->isAjax() && $this->input->method() !== 'post') {
            return $this->jsonResponse(false, 'Invalid request method.');
        }

        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->jsonResponse(false, 'Invalid policy slug.');
        }

        $content = $this->input->post('content', FALSE); // Allow HTML for rich text
        $default = get_default_legal_page($slug);
        $title = $default['title'];

        // Check if record exists in legal_pages
        $existing = $this->db->where('slug', $slug)->get('legal_pages')->row_array();

        if ($existing) {
            $updateData = [
                'draft_content' => $content,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
            $this->db->where('id', $existing['id'])->update('legal_pages', $updateData);
        } else {
            $insertData = [
                'slug'              => $slug,
                'title'             => $title,
                'draft_content'     => $content,
                'published_content' => null,
                'status'            => 'default',
                'published_at'      => null,
                'updated_at'        => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('legal_pages', $insertData);
        }

        $plainText = strip_tags($content);
        $wordCount = str_word_count($plainText);

        return $this->jsonResponse(true, 'Draft autosaved successfully.', [
            'saved_at'   => date('h:i:s A'),
            'word_count' => $wordCount,
        ]);
    }

    /**
     * AJAX endpoint to fetch read-only shipped default content
     *
     * @param string $slug
     */
    public function preview_default($slug = '')
    {
        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->jsonResponse(false, 'Invalid policy slug.');
        }

        $default = get_default_legal_page($slug);

        return $this->jsonResponse(true, 'Default policy content retrieved.', [
            'slug'    => $slug,
            'title'   => $default['title'],
            'content' => $default['content'],
        ]);
    }

    /**
     * AJAX endpoint to reset the editor draft back to shipped default
     *
     * @param string $slug
     */
    public function reset_to_default($slug = '')
    {
        if (!$this->isAjax() && $this->input->method() !== 'post') {
            return $this->jsonResponse(false, 'Invalid request method.');
        }

        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->jsonResponse(false, 'Invalid policy slug.');
        }

        $default = get_default_legal_page($slug);

        // Update draft_content to default in DB
        $existing = $this->db->where('slug', $slug)->get('legal_pages')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('legal_pages', [
                'draft_content' => $default['content'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->insert('legal_pages', [
                'slug'              => $slug,
                'title'             => $default['title'],
                'draft_content'     => $default['content'],
                'published_content' => null,
                'status'            => 'default',
                'published_at'      => null,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->jsonResponse(true, 'Draft reset to shipped default.', [
            'content'    => $default['content'],
            'word_count' => str_word_count(strip_tags($default['content'])),
        ]);
    }

    /**
     * AJAX endpoint to publish draft content to live published_content
     * Flips status from 'default' to 'custom'
     *
     * @param string $slug
     */
    public function publish($slug = '')
    {
        if (!$this->isAjax() && $this->input->method() !== 'post') {
            return $this->jsonResponse(false, 'Invalid request method.');
        }

        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->jsonResponse(false, 'Invalid policy slug.');
        }

        $content = $this->input->post('content', FALSE);
        $default = get_default_legal_page($slug);
        $title = $default['title'];

        // If content was not supplied in POST, read current draft_content
        $existing = $this->db->where('slug', $slug)->get('legal_pages')->row_array();
        if ($content === null || $content === '') {
            $content = ($existing && !empty($existing['draft_content']))
                ? $existing['draft_content']
                : $default['content'];
        }

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $this->db->where('id', $existing['id'])->update('legal_pages', [
                'published_content' => $content,
                'draft_content'     => $content,
                'status'            => 'custom',
                'published_at'      => $now,
                'updated_at'        => $now,
            ]);
        } else {
            $this->db->insert('legal_pages', [
                'slug'              => $slug,
                'title'             => $title,
                'draft_content'     => $content,
                'published_content' => $content,
                'status'            => 'custom',
                'published_at'      => $now,
                'updated_at'        => $now,
            ]);
        }

        return $this->jsonResponse(true, 'Policy published live successfully!', [
            'status'          => 'custom',
            'status_label'    => 'Customized',
            'published_at'    => date('d M Y, h:i A', strtotime($now)),
            'updated_at'      => date('d M Y, h:i A', strtotime($now)),
            'word_count'      => str_word_count(strip_tags($content)),
        ]);
    }

    /**
     * Unified Save Policy method (Single Save Button endpoint)
     * Saves content, marks it as custom/published live
     *
     * @param string $slug
     */
    public function save($slug = '')
    {
        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            if ($this->isAjax()) {
                return $this->jsonResponse(false, 'Invalid policy slug.');
            }
            $this->setMessage('danger', 'Invalid policy slug.');
            redirect('legal-pages');
            return;
        }

        $content = $this->input->post('content', FALSE);
        $default = get_default_legal_page($slug);
        $title = $default['title'];
        $now = date('Y-m-d H:i:s');

        $existing = $this->db->where('slug', $slug)->get('legal_pages')->row_array();
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('legal_pages', [
                'published_content' => $content,
                'draft_content'     => $content,
                'status'            => 'custom',
                'published_at'      => $now,
                'updated_at'        => $now,
            ]);
        } else {
            $this->db->insert('legal_pages', [
                'slug'              => $slug,
                'title'             => $title,
                'draft_content'     => $content,
                'published_content' => $content,
                'status'            => 'custom',
                'published_at'      => $now,
                'updated_at'        => $now,
            ]);
        }

        if ($this->isAjax()) {
            return $this->jsonResponse(true, 'Policy saved and updated successfully!', [
                'status'          => 'custom',
                'status_label'    => 'Customized',
                'published_at'    => date('d M Y, h:i A', strtotime($now)),
                'updated_at'      => date('d M Y, h:i A', strtotime($now)),
                'word_count'      => str_word_count(strip_tags($content)),
            ]);
        } else {
            $this->setMessage('success', "{$title} saved successfully!");
            redirect('legal-pages/edit/' . $slug);
        }
    }

    /**
     * AJAX endpoint to fetch default template content directly
     *
     * @param string $slug
     */
    public function get_default($slug = '')
    {
        $slug = strtolower(trim($slug));
        if (!in_array($slug, get_all_legal_page_slugs())) {
            return $this->jsonResponse(false, 'Invalid policy slug.');
        }

        $default = get_default_legal_page($slug);

        return $this->jsonResponse(true, 'Default template retrieved.', [
            'slug'    => $slug,
            'title'   => $default['title'],
            'content' => $default['content'],
        ]);
    }
}
