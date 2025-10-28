@extends('layouts.app')

@section('content')

<h3>Editar Cliente</h3>

<form action="{{ route('clientes.update', $cliente) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')
    @include('clientes.form')
    <button class="btn btn-outline-primary mt-2 btn-sm">💾Actualizar</button>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary mt-2 btn-sm ">↩️ Volver</a>
</form>

@endsection
