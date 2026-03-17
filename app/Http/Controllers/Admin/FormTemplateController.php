<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class FormTemplateController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::latest()->get();
        return Inertia::render('Admin/Forms/Index', [
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('form_templates', 'public');

        FormTemplate::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Đã tải lên biểu mẫu thành công.');
    }

    public function update(Request $request, FormTemplate $form)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('public')->exists($form->file_path)) {
                Storage::disk('public')->delete($form->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('form_templates', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $form->update($data);

        return back()->with('success', 'Đã cập nhật biểu mẫu thành công.');
    }

    public function destroy(FormTemplate $form)
    {
        if (Storage::disk('public')->exists($form->file_path)) {
            Storage::disk('public')->delete($form->file_path);
        }
        
        $form->delete();

        return back()->with('success', 'Đã xóa biểu mẫu thành công.');
    }

    public function download(FormTemplate $form)
    {
        if (!Storage::disk('public')->exists($form->file_path)) {
            abort(404, 'File không tồn tại.');
        }

        return Storage::disk('public')->download($form->file_path, $form->file_name);
    }
}
