@extends('adminlte::page')

@section('title', '- Cadastro de Usuário')
@section('plugins.select2', true)
@section('plugins.BsCustomFileInput', true)
@section('plugins.BootstrapSwitch', true)

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-fw fa-user"></i> Novo Usuário</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        @can('Listar Usuários')
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Usuários</a></li>
                        @endcan
                        <li class="breadcrumb-item active">Novo Usuário</li>
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
                            <h3 class="card-title">Dados Cadastrais do Usuário</h3>
                        </div>

                        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 form-group px-0">
                                        <label for="name">Nome</label>
                                        <input type="text" class="form-control" id="name"
                                            placeholder="Nome Completo" name="name" value="{{ old('name') }}" required>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                        <label for="telephone">Telefone</label>
                                        <input type="tel" class="form-control" id="telephone" placeholder="Telefone"
                                            name="telephone" value="{{ old('telephone') }}">
                                    </div>
                                    <div class="col-12 col-md-6 form-group px-0 pl-md-2">
                                        <label for="cell">Celular</label>
                                        <input type="tel" class="form-control" id="cell" placeholder="Celular"
                                            name="cell" value="{{ old('cell') }}">
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                        <x-adminlte-input-file name="photo" label="Foto"
                                            placeholder="Selecione uma imagem..." legend="Selecionar" />
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                        <label for="email">E-mail</label>
                                        <input type="email" class="form-control" id="email" placeholder="E-mail"
                                            name="email" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-12 col-md-6 form-group px-0 pl-md-2">
                                        <label for="password">Senha</label>
                                        <input type="password" class="form-control" id="password" placeholder="Senha"
                                            minlength="8" name="password" value="{{ old('password') }}" required>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between">
                                    @can('Atribuir Perfis')
                                        <div class="col-12 col-md-6 form-group px-0 pr-md-2">
                                            <label for="role">Tipo de Usuário</label>
                                            <x-adminlte-select2 name="role">
                                                @foreach ($roles as $role)
                                                    <option {{ old('role') == $role->name ? 'selected' : '' }}
                                                        value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </x-adminlte-select2>
                                        </div>
                                    @endcan
                                </div>

                                <div class="col-12 col-md-6 form-group px-0 pr-md-2 d-flex flex-wrap justify-content-start">
                                    <x-adminlte-input-switch name="first_access" label="Primeiro Acesso" data-on-text="Sim"
                                        data-off-text="Não" data-on-color="teal" id="first_access" />
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('custom_js')
    <script src="{{ asset('vendor/jquery/jquery.inputmask.bundle.min.js') }}"></script>
    @vite('resources/js/phone.js')
@endsection


