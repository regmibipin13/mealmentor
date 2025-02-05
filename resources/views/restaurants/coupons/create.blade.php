@extends('restaurants.app')
@section('content')
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Coupons Create Form</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('restaurants.coupons.store', [currentRestaurant()->slug ?? uniqid()]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf

                            @include('restaurants.coupons.form')
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
