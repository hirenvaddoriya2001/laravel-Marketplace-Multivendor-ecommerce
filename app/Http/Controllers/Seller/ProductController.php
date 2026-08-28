<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Rules\ValidatePrice;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function addProduct(Request $request){
       $data = [
          'pageTitle'=>'Add Product',
          'categories'=>Category::orderBy('category_name','asc')->get()
       ];
       return view('back.pages.seller.add-product',$data);
    } //End Method

    public function getProductCategory(Request $request){
        $category_id = $request->category_id;
        $category = Category::findOrFail($category_id);
        $subcategories = SubCategory::where('category_id',$category_id)
                                    ->where('is_child_of',0)
                                    ->orderBy('subcategory_name','asc')
                                    ->get();

        $html = '';
        foreach( $subcategories as $item ){
            $html.='<option value="'.$item->id.'">'.$item->subcategory_name.'</option>';
            if( count($item->children) > 0 ){
                foreach( $item->children as $child ){
                    $html.='<option value="'.$child->id.'">-- '.$child->subcategory_name.'</option>';
                }
            }
        }
        return response()->json(['status'=>1,'data'=>$html]);
    } //End Method

    public function createProduct(Request $request){
        /**
         * VALIDATE THE FORM
         * -----------------
         */
       $request->validate([
    'name' => [
        'required',
        'unique:products,name,'
    ],

    'summary' => [
        'required',
        'min:100',
    ],

    'product_image' => [
        'nullable',
        'image',
        'mimes:png,jpg,jpeg,webp',
        'max:2048',
    ],

    'category' => [
        'required',
        'exists:categories,id',
    ],

    'subcategory' => [
        'required',
        'exists:sub_categories,id',
    ],

    'price' => [
        'required',
        new ValidatePrice,
    ],

    'compare_price' => [
        'nullable',
        new ValidatePrice,
    ],

    'sku' => [
        'nullable',
        'string',
        'max:100',
        'unique:products,sku,'
    ],

    'manage_stock' => [
        'nullable',
        'boolean',
    ],

    'stock_quantity' => [
        'required_if:manage_stock,1',
        'nullable',
        'integer',
        'min:0',
    ],

    'low_stock_threshold' => [
        'required_if:manage_stock,1',
        'nullable',
        'integer',
        'min:0',
    ],

    'visibility' => [
        'required',
        'boolean',
    ],
], [
    'name.required' => 'Enter product name.',
    'name.unique' => 'This product name is already taken.',
    'summary.required' => 'Write a product summary.',
    'summary.min' => 'The product summary must contain at least 100 characters.',
    'product_image.image' => 'The selected file must be an image.',
    'category.required' => 'Select a product category.',
    'subcategory.required' => 'Select a product subcategory.',
    'price.required' => 'Enter the product price.',
    'sku.unique' => 'This SKU is already assigned to another product.',
    'stock_quantity.required_if' => 'Enter the available stock quantity.',
    'stock_quantity.integer' => 'Stock quantity must be a whole number.',
    'stock_quantity.min' => 'Stock quantity cannot be negative.',
    'low_stock_threshold.required_if'
        => 'Enter the low-stock warning quantity.',
    'low_stock_threshold.integer'
        => 'Low-stock threshold must be a whole number.',
]);

        $product_image = null;
        if( $request->hasFile('product_image') ){
            $path = 'images/products/';
            $file = $request->file('product_image');
            $filename = 'PIMG_'.time().uniqid().'.'.$file->getClientOriginalExtension();

            // $upload = $file->move(public_path($path), $filename);
            $maxWidth = 1080;
            $maxHeight = 1080;
            $full_path = $path.$filename;
            $image = Image::make($file->path());
    
            $image->height() > $image->width() ? $maxWidth = null : $maxHeight = null;
            $image->fit($maxWidth, $maxHeight, function($constraint){
                  $constraint->upsize();
            });
            $upload = $image->save($full_path);
            

            if( $upload ){
                $product_image = $filename;
            }
        }

        // normalize the SKU
        $sku = filled($request->sku)
    ? strtoupper(trim($request->sku))
    : null;

        //SAVE PRODUCT DETAILS
        $product = new Product();
        $product->user_type = 'seller';
        $product->seller_id = auth('seller')->id();
        $product->name = $request->name;
        $product->summary = $request->summary;
        $product->category = $request->category;
        $product->subcategory = $request->subcategory;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->visibility = $request->visibility;
        $product->product_image = $product_image;
        $product->sku = $sku;
        $product->manage_stock = $request->boolean('manage_stock');
        $product->stock_quantity = $product->manage_stock
            ? (int) $request->stock_quantity
            : 0;

        $product->low_stock_threshold = $product->manage_stock
            ? (int) $request->low_stock_threshold
            : 0;
        $saved = $product->save();


        if( $saved ){
            return response()->json(['status'=>1,'msg'=>'New product has been successfully created.']);
        }else{
            return response()->json(['status'=>0,'msg'=>'Something went wrong.']);
        }
    } //End Method

    public function allProducts(Request $request){
        $data = [
            'pageTitle'=>'My Products'
        ];
        return view('back.pages.seller.products',$data);
    } //End Method

    public function editProduct(Request $request){
       $product = Product::findOrFail($request->id);
       $categories = Category::orderBy('category_name','asc')->get();
       $subcategories = SubCategory::where('category_id',$product->category)
                                   ->where('is_child_of',0)
                                   ->orderBy('subcategory_name','asc')
                                   ->get();
        $data = [
            'pageTitle'=>'Edit Product',
            'categories'=>$categories,
            'subcategories'=>$subcategories,
            'product'=>$product
        ];
        return view('back.pages.seller.edit-product',$data);
    } //End Method

   public function updateProduct(Request $request)
{
    // Ensure the authenticated seller owns this product.
    $product = Product::where(
        'seller_id',
        auth('seller')->id()
    )->findOrFail($request->product_id);

    $request->validate([
        'name' => [
            'required',
            'unique:products,name,'.$product->id,
        ],

        'summary' => [
            'required',
            'min:100',
        ],

        'product_image' => [
            'nullable',
            'image',
            'mimes:png,jpg,jpeg,webp',
            'max:2048',
        ],

        'category' => [
            'required',
            'exists:categories,id',
        ],

        'subcategory' => [
            'required',
            'exists:sub_categories,id',
        ],

        'price' => [
            'required',
            new ValidatePrice,
        ],

        'compare_price' => [
            'nullable',
            new ValidatePrice,
        ],

        'sku' => [
            'nullable',
            'string',
            'max:100',
            'unique:products,sku,'.$product->id,
        ],

        'manage_stock' => [
            'nullable',
            'boolean',
        ],

        'stock_quantity' => [
            'required_if:manage_stock,1',
            'nullable',
            'integer',
            'min:0',
        ],

        'low_stock_threshold' => [
            'required_if:manage_stock,1',
            'nullable',
            'integer',
            'min:0',
        ],

        'visibility' => [
            'required',
            'boolean',
        ],
    ], [
        'name.required' => 'Enter product name.',
        'name.unique' => 'This product name is already taken.',
        'summary.required' => 'Write a product summary.',
        'summary.min'
            => 'The product summary must contain at least 100 characters.',
        'product_image.image'
            => 'The selected file must be an image.',
        'product_image.mimes'
            => 'The product image must be JPG, JPEG, PNG, or WebP.',
        'product_image.max'
            => 'The product image must not be larger than 2 MB.',
        'category.required'
            => 'Select a product category.',
        'subcategory.required'
            => 'Select a product subcategory.',
        'price.required'
            => 'Enter the product price.',
        'sku.unique'
            => 'This SKU is already assigned to another product.',
        'stock_quantity.required_if'
            => 'Enter the available stock quantity.',
        'stock_quantity.integer'
            => 'Stock quantity must be a whole number.',
        'stock_quantity.min'
            => 'Stock quantity cannot be negative.',
        'low_stock_threshold.required_if'
            => 'Enter the low-stock warning quantity.',
        'low_stock_threshold.integer'
            => 'Low-stock threshold must be a whole number.',
    ]);

    $productImage = $product->product_image;

    // Upload a replacement primary product image.
    $productImage = null;

if ($request->hasFile('product_image')) {
    $file = $request->file('product_image');

    $filename = 'PIMG_'
        .time()
        .uniqid()
        .'.'
        .$file->getClientOriginalExtension();

    $directory = public_path('images/products');

    if (! File::isDirectory($directory)) {
        File::makeDirectory(
            $directory,
            0755,
            true
        );
    }

    $fullPath = $directory
        .DIRECTORY_SEPARATOR
        .$filename;

    $maxWidth = 1080;
    $maxHeight = 1080;

    $image = Image::make($file->path());

    if ($image->height() > $image->width()) {
        $maxWidth = null;
    } else {
        $maxHeight = null;
    }

    $image->fit(
        $maxWidth,
        $maxHeight,
        function ($constraint) {
            $constraint->upsize();
        }
    );

    $image->save($fullPath);

    $productImage = $filename;
}

    // Normalize the SKU.
    $sku = filled($request->sku)
        ? strtoupper(trim($request->sku))
        : null;

    $manageStock = $request->boolean('manage_stock');

    // Update product details.
    $product->name = $request->name;

    // Setting this to null lets the sluggable package regenerate it.
    $product->slug = null;

    $product->summary = $request->summary;
    $product->category = $request->category;
    $product->subcategory = $request->subcategory;
    $product->price = $request->price;
    $product->compare_price = $request->compare_price;
    $product->visibility = $request->visibility;
    $product->product_image = $productImage;

    // Inventory details.
    $product->sku = $sku;
    $product->manage_stock = $manageStock;

    $product->stock_quantity = $manageStock
        ? (int) $request->stock_quantity
        : 0;

    $product->low_stock_threshold = $manageStock
        ? (int) $request->low_stock_threshold
        : 0;

    $updated = $product->save();

    if ($updated) {
        return response()->json([
            'status' => 1,
            'msg' => 'Product and inventory were successfully updated.',
        ]);
    }

    return response()->json([
        'status' => 0,
        'msg' => 'Something went wrong while updating the product.',
    ]);
}// End Method

    public function uploadProductImages(Request $request){
        $product = Product::findOrFail($request->product_id);
        $path = "images/products/additionals/";
        $file = $request->file('file');
        $filename = 'APIMG_'.$product->id.time().uniqid().'.'.$file->getClientOriginalExtension();

        //Upload image(s)
        // $file->move(public_path($path), $filename);
        $maxWidth = 1080;
        $maxHeight = 1080;
        $full_path = $path.$filename;
        $image = Image::make($file->path());

        $image->height() > $image->width() ? $maxWidth = null : $maxHeight = null;
        $image->fit($maxWidth, $maxHeight, function($constraint){
              $constraint->upsize();
        });
        $image->save($full_path);

        //Save image(s) path/name
        $pimage = new ProductImage();
        $pimage->product_id = $product->id;
        $pimage->image = $filename;
        $pimage->save();
    } // End Method

    public function getProductImages(Request $request){
        $product = Product::with('images')->findOrFail($request->product_id);
        $path = "images/products/additionals/";
        $html = "";
        if( $product->images->count() > 0 ){
            foreach( $product->images as $item ){
                $html.='<div class="box">
                   <img src="/'.$path.$item->image.'">
                   <a href="javascript:;" data-image="'.$item->id.'" class="btn btn-danger btn-sm" id="deleteProductImageBtn"><i class="fa fa-trash"></i></a>
                </div>';
            }
        }else{
            $html = '<span class="text-danger">No image(s)</span>';
        }

        return response()->json(['status'=>1,'data'=>$html]);
    } // End Method

    public function deleteProductImage(Request $request){
        $product_image = ProductImage::findOrFail($request->image_id);
        $path = "images/products/additionals/";

        if( $product_image->image != null && File::exists(public_path($path.$product_image->image)) ){
            File::delete(public_path($path.$product_image->image));
        }
        $delete = $product_image->delete();

        if( $delete ){
            return response()->json(['status'=>1,'msg'=>'Product image has been successfully deleted.']);
        }else{
            return response()->json(['status'=>0,'msg'=>'Something went wrong.']);
        }
    } //End Method

    public function deleteProduct(Request $request){
        $product = Product::with('images')->findOrFail($request->product_id);

        //First, check if this product has additionals image(s) and delete them
        if( $product->images->count() > 1 ){
            $images_path = 'images/products/additionals/';
            foreach( $product->images as $item ){
                if( $item->image != null && File::exists(public_path($images_path.$item->image)) ){
                    File::delete(public_path($images_path.$item->image));
                }
                $pimage = ProductImage::findOrFail($item->id);
                $pimage->delete();
            }
        }

        //Delete actual product
        $path = 'images/products/';
        $product_image = $product->product_image;
        if( $product_image != null && File::exists(public_path($path.$product_image)) ){
            File::delete(public_path($path.$product_image));
        }
        $delete = $product->delete();

        if( $delete ){
            return response()->json(['status'=>1,'msg'=>'Product has been successfully deleted.']);
        }else{
            return response()->json(['status'=>0,'msg'=>'Something went wrong.']);
        }
    }
}
