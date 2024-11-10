<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Upload ảnh và trả về link ảnh.
     */
    public function uploadImage(Request $request)
    {
        // Xác thực file upload
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10000000' // Chỉ cho phép các file ảnh với kích thước tối đa 2MB
        ]);

        // Lưu file vào thư mục 'uploads' trong storage/app/public
        $path = $request->file('image')->store('uploads', 'public');

        // Tạo URL cho file đã lưu
        $url = Storage::url($path);

        // Trả về đường dẫn của file đã upload
        return response()->json([
            'message' => 'Image uploaded successfully',
            'url' => asset($url) // asset() để tạo URL đầy đủ
        ]);
    }
}
