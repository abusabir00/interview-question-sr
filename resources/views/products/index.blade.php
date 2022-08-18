@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="/search" method="post" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                    <option  value="" disabled selected > --Select A Variant-- </option>
                        @foreach ($variants as $variant)
                            <optgroup label="{{ $variant[0]->v_title }}">
                                @foreach ($variant as $val)
                                    <option value="{{ $val->id }}">{{ $val->variant }}</option>
                                @endforeach
                            </optgroup> 
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="number" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="number" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th width="20%">Title</th>
                        <th width="25%">Description</th>
                        <th width="50%">Variant</th>
                        <th width="5%">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($product_list as $key => $product )
                        <tr>
                            <td>{{ $key + 1}}</td>
                            <td>{{$product->title}} <br> Created at : {{ date('d-F-Y',strtotime($product->created_at))  }}</td>
                            <td>{{substr($product->description,0,40)}}</td>
                            <td>
                                
                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant{{$key}}">
                                @foreach ($product->prices as $price )
                                    <dt class="col-sm-3 pb-0">
                                        <?php 
                                             $one = ''; $two = ''; $three = '';
                                            foreach($product->variant as $variant){
                                                if($price->product_variant_one && $variant->id === $price->product_variant_one){
                                                    $one = $variant->variant . '/';
                                                }
                                                if($price->product_variant_two && $variant->id === $price->product_variant_two){
                                                    $two = $variant->variant . '/';
                                                }
                                                if($price->product_variant_three && $variant->id === $price->product_variant_three){
                                                    $three = $variant->variant;
                                                }   
                                            }
                                        ?>
                                        {{  $one .  $two . $three }}
                                    </dt>
                                    <dd class="col-sm-9">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 pb-0">Price : {{ number_format($price->price,2) }}</dt>
                                            <dd class="col-sm-8 pb-0">InStock : {{ number_format($price->stock,2) }}</dd>
                                        </dl>
                                    </dd>
                                    @endforeach
                                </dl>
                                
                                <button onclick="$('#variant{{$key}}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing 
                        {{ $product_list->firstItem() }} 
                        to {{ $product_list->lastItem() }} 
                        out of {{ $product_list->total() }}</p>
                </div>
                <div class="col-md-6">
                    {{ $product_list->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
