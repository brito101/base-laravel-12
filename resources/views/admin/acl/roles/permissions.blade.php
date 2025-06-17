@extends('adminlte::page')

@section('title', '- Sicronizar Permissões')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-fw fa-user-cog"></i> Sincronizar Permissões para: {{ $role->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">ACL</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.role.index') }}">Perfis</a></li>
                        <li class="breadcrumb-item active">Sincronizar Permissões</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    @include('components.alert')

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Permissões para: {{ $role->name }}</h3>
                        </div>

                        <form action="{{ route('admin.role.permissionsSync', ['role' => $role->id]) }}" method="post"
                            autocomplete="off">
                            @csrf
                            @method('PUT')
                            <div class="card-body d-flex flex-wrap">
                                @foreach ($permissions as $permission)
                                    <div class="col-12 col-md-4">
                                        <div class="card p-0">
                                            <div class="card-body icheck-primary">
                                                <input type="checkbox" style="cursor: pointer" id="{{ $permission->id }}"
                                                    name="{{ $permission->id }}"
                                                    {{ $permission->can == '1' ? 'checked' : '' }}>
                                                <label for="{{ $permission->id }}" class="my-0 ml-2">{{ $permission->name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Sincronizar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('adminlte_js')
    <script src="{{ asset('vendor/jquery/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ asset('js/company.js') }}"></script>
@endsection
