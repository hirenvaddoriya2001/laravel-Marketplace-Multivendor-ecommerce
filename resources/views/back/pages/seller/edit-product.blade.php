@extends('back.layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page title here')
@section('content')

<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Edit product</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('seller.home') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Edit product
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="{{ route('seller.product.all-products') }}" class="btn btn-primary">View all products</a>
        </div>
    </div>
</div>

<form action="{{ route('seller.product.update-product') }}" method="POST" enctype="multipart/form-data" id="updateProductForm">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <div class="row pd-10">
        <div class="col-md-8 mb-20">
            <div class="card-box height-100-p pd-20" style="position: relative">
                <div class="form-group">
                    <label for=""><b>Product name:</b></label>
                    <input type="text" class="form-control" name="name" placeholder="Enter product name" value="{{ $product->name }}">
                    <span class="text-danger error-text name_error"></span>
                </div>
                <div class="form-group">
                    <label for=""><b>Product summary:</b></label>
                    <textarea  id="summary" class="form-control summernote" cols="30" rows="10">{!! $product->summary !!}</textarea>
                    <span class="text-danger error-text summary_error"></span>
                </div>
                <div class="form-group">
                    <label for=""><b>Product image:</b><small>Must be square and maximum dimension of (1080x1080)</small></label>
                    <input type="file" name="product_image" class="form-control">
                    <span class="text-danger error-text product_image_error"></span>
                </div>
                <div class="d-block mb-3" style="max-width: 250px;">
                  <img src="" class="img-thumbnail" id="image-preview" data-ijabo-default-img="/images/products/{{ $product->product_image }}">
                </div>
                <b>NB</b>:<small class="text-danger">You will be able to add more images related to this product when you are on edit product page.</small>
            </div>
        </div>
        <div class="col-md-4 mb-20">
            <div class="card-box min-height-200px pd-20 mb-20">
                <div class="form-group">
                    <label for=""><b>Category:</b></label>
                    <select name="category" id="category" class="form-control">
                       
                        @foreach ($categories as $item)
                            <option value="{{ $item->id }}" {{ $product->category == $item->id ? 'selected' : '' }}>{{ $item->category_name }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger error-text category_error"></span>
                </div>

                <div class="form-group">
                    <label for=""><b>Sub Category:</b></label>
                    <select name="subcategory" id="subcategory" class="form-control">
                        <option value="" selected>Not Set</option>
                        @foreach ($subcategories as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $product->subcategory ? 'selected' : '' }}>{{ $item->subcategory_name }}</option>

                            @if ( count($item->children) > 0 )
                                @foreach ($item->children as $child)
                                    <option value="{{ $child->id }}" {{ $child->id == $product->subcategory ? 'selected' : '' }}>-- {{ $child->subcategory_name }}</option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                    <span class="text-danger error-text subcategory_error"></span>
                </div>

            </div>
            <div class="card-box min-height-200px pd-20 mb-20">
                <div class="form-group">
                    <label for=""><b>Price:</b><small>In Dollar Currency ($)</small></label>
                    <input type="text" name="price" class="form-control" placeholder="Eg: 23.99" value="{{ $product->price }}">
                    <span class="text-danger error-text price_error"></span>
                </div>
                <div class="form-group">
                    <label for=""><b>Compare Price:</b><small>Optional</small></label>
                    <input type="text" name="compare_price" class="form-control" placeholder="Eg: 77.99" value="{{ $product->compare_price }}">
                    <span class="text-danger error-text compare_price_error"></span>
                </div>
            </div>
            <div class="card-box min-height-200px pd-20 mb-20">
                <div class="col-md-6">
                <div class="form-group">
                    <label for="sku">
                        SKU
                        <small class="text-muted">(optional)</small>
                    </label>

                    <input
                        id="sku"
                        type="text"
                        name="sku"
                        class="form-control"
                        value="{{ old('sku', $product->sku) }}"
                        placeholder="Example: PRODUCT-0001"
                    >

                    <span class="text-danger error-text sku_error"></span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="manage_stock">Inventory tracking</label>

                    <select
                        id="manage_stock"
                        name="manage_stock"
                        class="form-control"
                    >
                        <option
                            value="1"
                            @selected(old('manage_stock', $product->manage_stock) == 1)
                        >
                            Track stock quantity
                        </option>

                        <option
                            value="0"
                            @selected(old('manage_stock', $product->manage_stock) == 0)
                        >
                            Always available
                        </option>
                    </select>

                    <span class="text-danger error-text manage_stock_error"></span>
                </div>
            </div>

            <div class="col-md-6 inventory-field">
                <div class="form-group">
                    <label for="stock_quantity">Available quantity</label>

                    <input
                        id="stock_quantity"
                        type="number"
                        name="stock_quantity"
                        class="form-control"
                        value="{{ old('stock_quantity', $product->stock_quantity) }}"
                        min="0"
                        step="1"
                    >

                    <span class="text-danger error-text stock_quantity_error"></span>
                </div>
            </div>

            <div class="col-md-6 inventory-field">
                <div class="form-group">
                    <label for="low_stock_threshold">
                        Low-stock warning level
                    </label>

                    <input
                        id="low_stock_threshold"
                        type="number"
                        name="low_stock_threshold"
                        class="form-control"
                        value="{{ old(
                            'low_stock_threshold',
                            $product->low_stock_threshold
                        ) }}"
                        min="0"
                        step="1"
                    >

                    <span class="text-danger error-text low_stock_threshold_error"></span>
                </div>
            </div>
            </div>
            <div class="card-box min-height-120px pd-20">
               <div class="form-group">
                 <label for=""><b>Visibilty:</b></label>
                 <select name="visibility" id="" class="form-control">
                    <option value="1" {{ $product->visibility == 1 ? 'selected' : '' }}>Public</option>
                    <option value="0" {{ $product->visibility == 0 ? 'selected' : '' }}>Private</option>
                 </select>
               </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Update product</button>
    </div>
</form>
<hr>
<div class="row">
    <div class="col-12">
        <div class="card-box min-height-200px pd-20 mb-20">
            <div class="title mb-2">
                <h6>Additional Product Images</h6>
            </div>
            <form action="{{ route('seller.product.upload-images',['product_id'=>request('id')]) }}" class="dropzone">
             @csrf
            </form>
            <button class="btn btn-outline-primary btn-sm mt-2" id="uploadAdditionalImagesBtn">Upload</button>
        </div>
    </div>
    <div class="box-container mb-2" id="product_images">
   
    </div>
</div>

@endsection
@push('stylesheets')
    <link rel="stylesheet" href="/extra-assets/dropzonejs/min/dropzone.min.css">
    <style>
        .box-container{
            width: 100%;
            display: flex;
            flex-direction: row;
            gap: 1rem;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .box-container .box{
            background: #423838;
            display: block;
            width: 110px;
            height: 110px;
            position: relative;
            overflow: hidden;
        }
        .box-container .box img{
            width: 100%;
            height: auto;
        }

        .box-container .box a{
            position: absolute;
            right: 7px;
            bottom: 5px;
        }

        .swal2-popup{
            font-size: .87em;
        }
    </style>
@endpush
@push('scripts')
<script src="/extra-assets/dropzonejs/min/dropzone.min.js"></script>
    <script>
        //List sub categories according to the selected category
        $(document).on('change','select#category', function(e){
            e.preventDefault();
            var category_id = $(this).val();
            var url = "{{ route('seller.product.get-product-category') }}";
            if( category_id == '' ){
                $("select#subcategory").find("option").not(":first").remove();
            }else{
                $.get(url,{ category_id:category_id }, function(response){
                   $("select#subcategory").find("option").not(":first").remove();
                   $("select#subcategory").append(response.data);
                },'JSON');
            }
        });

        //Preview selected product image
        $('input[type="file"][name="product_image"]').on(
            'change',
            function () {
                const input = this;
                const preview = document.querySelector('#image-preview');
                const allowedTypes = [
                    'image/png',
                    'image/jpeg',
                    'image/webp'
                ];

                if (!input.files || !input.files[0]) {
                    return;
                }

                const file = input.files[0];

                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a JPG, JPEG, PNG, or WebP image.');
                    input.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('The product image must not be larger than 2 MB.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {
                    preview.src = event.target.result;
                };

                reader.readAsDataURL(file);
            }
        );

        //SUBMIT PRODUCT FORM
        $('#updateProductForm').on('submit', function(e){
           e.preventDefault();
           var summary = $('textarea.summernote').summernote('code');
           var form = this;
           var formdata = new FormData(form);
               formdata.append('summary',summary);

           $.ajax({
              url:$(form).attr('action'),
              method:$(form).attr('method'),
              data:formdata,
              processData:false,
              dataType:'json',
              contentType:false,
              beforeSend:function(){
                toastr.remove();
                $(form).find('span.error-text').text('');
              },
              success:function(response){
                  toastr.remove();
                  if( response.status == 1 ){
                    // $(form)[0].reset();
                    // $('textarea.summernote').summernote('code','');
                    // $('select#subcategory').find('option').not(':first').remove();
                    // $('img#image-preview').attr('src','');
                    toastr.success(response.msg);
                  }else{
                    toastr.error(response.msg);
                  }
              },
              error: function (response) {
                    toastr.remove();

                    if (
                        response.status === 422 &&
                        response.responseJSON &&
                        response.responseJSON.errors
                    ) {
                        $.each(
                            response.responseJSON.errors,
                            function (prefix, messages) {
                                $(form)
                                    .find('span.' + prefix + '_error')
                                    .text(messages[0]);
                            }
                        );

                        toastr.error('Please correct the highlighted fields.');

                        return;
                    }

                    console.error(response.responseText);

                    toastr.error(
                        'The product could not be created. Check the browser console or Laravel log.'
                    );
                }
           });
        });


        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone('.dropzone', {
            autoProcessQueue:false,
            parallelUploads:1, //Number of files proccessed at a time
            addRemoveLinks:true,
            maxFilesize:2, // 2MB
            acceptedFiles:'image/*',
            init:function(){
                thisDz = this;
                var uploadBtn = document.getElementById('uploadAdditionalImagesBtn');
                uploadBtn.addEventListener('click', function(){
                    var nFiles = myDropzone.getQueuedFiles().length;
                    thisDz.options.parallelUploads = nFiles;
                    thisDz.processQueue();
                });

                thisDz.on('queuecomplete', function(){
                    this.removeAllFiles();
                    getProductImages();
                });
            }
        });
        getProductImages();
        function getProductImages(){
            var url = "{{ route('seller.product.get-product-images',['product_id'=>request('id')]) }}";
            $.get(url,{}, function(response){
               $('div#product_images').html(response.data);
            },'json');
        }

        $(document).on('click','#deleteProductImageBtn', function(e){
            e.preventDefault();
            var url = "{{ route('seller.product.delete-product-image') }}";
            var token = "{{ csrf_token() }}";
            var image_id = $(this).data("image");
            swal.fire({
                title:'Are you sure?',
                html:'You want to delete this image',
                showCloseButton:true,
                showCancelButton:true,
                cancelButtonText:'Cancel',
                confirmButtonText:'Yes, Delete',
                cancelButtonColor:'#d33',
                confirmButtonColor:'#3085d6',
                width:300,
                allowOutsideClick:false,
            }).then(function(result){
                if( result.value ){
                    $.post(url, { _token:token, image_id:image_id }, function(response){
                       toastr.remove();
                       if( response.status == 1 ){
                          getProductImages();
                          toastr.success(response.msg);
                       }else{
                          toastr.error(response.msg);
                       }
                    },'json');
                }
            });
        });


        function toggleInventoryFields() {
            const shouldTrack = $('#manage_stock').val() === '1';

            $('.inventory-field').toggle(shouldTrack);

            $('#stock_quantity, #low_stock_threshold')
                .prop('disabled', !shouldTrack);
        }

        $('#manage_stock').on('change', toggleInventoryFields);

        toggleInventoryFields();
</script>

   
@endpush