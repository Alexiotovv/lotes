@extends('layouts.app')

@section('content')
<h3>Editar Cotización</h3>

<form action="{{ route('cotizaciones.update', $cotizacione) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')
    @include('cotizaciones.form')
    <button class="btn btn-outline-primary mt-2 btn-sm">💾 Actualizar</button>
    <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary mt-2 btn-sm">↩️ Volver</a>
</form>
@endsection
