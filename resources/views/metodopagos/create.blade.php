@extends('layouts.app')

@section('content')
<h3>Registrar Método de Pago</h3>

<form action="{{ route('metodopagos.store') }}" method="POST" class="mt-3">
    @csrf
    @include('metodopagos.form')
    <button class="btn btn-outline-success mt-2 btn-sm">💾 Guardar</button>
    <a href="{{ route('metodopagos.index') }}" class="btn btn-outline-secondary mt-2 btn-sm">↩️ Volver</a>
</form>
@endsection
