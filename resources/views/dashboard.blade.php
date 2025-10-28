@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Dashboard</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
                <div class="card-header">Total de Lotes</div>
                <div class="card-body"><h3>{{ $totalLotes }}</h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-header">Lotes Vendidos</div>
                <div class="card-body"><h3>{{ $vendidos }}</h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning mb-3">
                <div class="card-header">Ingresos Estimados</div>
                <div class="card-body"><h3>S/ {{ number_format($ingresos, 2) }}</h3></div>
            </div>
        </div>
    </div>
</div>
@endsection