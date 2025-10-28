@extends('layouts.app')

@section('content')
<h3>Registrar Cliente</h3>

<form action="{{ route('clientes.store') }}" method="POST" class="mt-3">
    @csrf
    @include('clientes.form')
    <button class="btn btn-outline-success mt-2 btn-sm">💾 Guardar</button>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary mt-2 btn-sm">↩️ Volver</a>
</form>
@endsection
