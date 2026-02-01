{{-- resources/views/students/show.blade.php --}}

@extends('layout')

@section('content')
<div class="container mt-5">
    <h2>Student Detail</h2>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $student->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $student->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $student->email }}</td>
        </tr>
        <tr>
            <th>Age</th>
            <td>{{ $student->age }}</td>
        </tr>
    </table>

    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning">Edit</a>

    <form action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>

    <a href="{{ route('students.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
