@extends('layouts.app')

@section('title', '¡Solicitud recibida! — Cristina Spa')

@section('head')
    <meta name="robots" content="noindex, nofollow">
    @include('agendar._styles')
@endsection

@section('content')

    <section class="nosotros-hero agendar-hero">
        <div class="nosotros-hero-inner">
            <span class="section-label">Solicitud enviada</span>
            <h1 class="nosotros-hero-title">¡Gracias por elegirnos!</h1>
            <p class="nosotros-hero-sub">Recibimos tu solicitud de cita. En breve nuestro equipo se comunicará contigo por WhatsApp o llamada para confirmar la hora.</p>
        </div>
    </section>

    <section class="contact agendar">
        <div class="contact-inner agendar-inner" style="grid-template-columns:1fr;max-width:520px;text-align:center;">
            <div class="contact-form">
                <p class="contact-form-eyebrow">Horario de atención</p>
                <h3 class="contact-form-title">Lunes a sábado · 9:00 – 20:00</h3>
                <a href="{{ route('home') }}" class="submit-button ag-submit" style="text-decoration:none;">Volver al inicio</a>
            </div>
        </div>
    </section>

@endsection
