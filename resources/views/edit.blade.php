{{-- resources/views/students/edit.blade.php --}}

@extends('layout')

@section('content')
<div class="container mt-5">
    <h2>Edit Student</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name:</label>
            <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                value="{{ old('name', $student->name) }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                value="{{ old('email', $student->email) }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="age">Age:</label>
            <input
                type="number"
                class="form-control"
                id="age"
                name="age"
                value="{{ old('age', $student->age) }}"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
