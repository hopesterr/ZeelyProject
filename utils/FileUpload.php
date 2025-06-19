<?php
/**
 * File Upload Utility
 */

class FileUpload {
    private $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $max_size;
    private $upload_path;

    public function __construct($upload_path = 'uploads/', $max_size = null) {
        $this->upload_path = $upload_path;
        $this->max_size = $max_size ?: MAX_FILE_SIZE;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
    }

    public function uploadImage($file, $prefix = 'img_') {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $this->allowed_types)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        // Validate file size
        if ($file['size'] > $this->max_size) {
            throw new Exception('File size too large. Maximum size is ' . ($this->max_size / 1024 / 1024) . 'MB.');
        }

        // Generate unique filename
        $extension = $this->getExtensionFromMimeType($mime_type);
        $filename = $prefix . uniqid() . '.' . $extension;
        $filepath = $this->upload_path . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return $filepath;
    }

    private function getExtensionFromMimeType($mime_type) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        return $extensions[$mime_type] ?? 'jpg';
    }

    public function deleteFile($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
?>