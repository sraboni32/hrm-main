@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Job Vacancies</h3>
                </div>

                <div class="card-body">
                    @if(count($job_vacancies) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($job_vacancies as $job)
                                        <tr>
                                            <td>{{ $job->title }}</td>
                                            <td>{{ $job->description }}</td>
                                            <td>
                                                <a href="{{ $job->link }}" class="btn btn-primary btn-sm" target="_blank">
                                                    Apply Now
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No job vacancies available at the moment.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 