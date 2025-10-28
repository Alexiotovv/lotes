@extends('layouts.app')

@section('content')
<h3>Editar Método de Pago</h3>

<form action="{{ route('metodopagos.update', $metodopago) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')
    @include('metodopagos.form')
    <button class="btn btn-outline-primary mt-2 btn-sm">💾 Actualizar</button>
    <a href="{{ route('metodopagos.index') }}" class="btn btn-outline-secondary mt-2 btn-sm">↩️ Volver</a>
</form>
@endsection
