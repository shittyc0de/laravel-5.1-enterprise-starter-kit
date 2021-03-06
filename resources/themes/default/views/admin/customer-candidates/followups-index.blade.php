@extends('layouts.master')

@section('head_extra')
    <!-- Select2 css -->
    @include('partials._head_extra_select2_css')
    <!-- NProgress 0.2.0  -->
    <link rel="stylesheet" href="{{ asset('/bower_components/nprogress/nprogress.css') }}" media="screen" charset="utf-8">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('admin/customer-candidates/followup.page.index.table-title') }}</h3>
                    &nbsp;
                    {!! Form::select( 'candidateType', config('constant.customer-types'), '', [ 'style' => 'max-width:150px;', 'id' => 'select-candidate-type', 'class' => 'select-candidate-type', '_token' => csrf_token()] ) !!}
                    &nbsp;
                    <a class="btn btn-default btn-sm" id="search" href="#" title="{{ trans('admin/customer-candidates/followup.button.search') }}">
                        <i class="fa fa-search"></i>
                    </a>

                    <div class="box-tools pull-right">
                        <label class="label label-info">{{ $candidateFollowups->count() }}</label>
                        <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="example2" class="table table-hover">
                            <thead>
                                <tr>
                                    <!-- <th style="text-align: center">
                                        <a class="btn" href="#" onclick="toggleCheckbox(); return false;" title="{{ trans('general.button.toggle-select') }}">
                                            <i class="fa fa-check-square-o"></i>
                                        </a>
                                    </th> -->
                                    <th>{{ trans('admin/customer-candidates/general.columns.name') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.type') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.phone') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.address') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.created') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.actions') }}</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <!-- <th style="text-align: center">
                                        <a class="btn" href="#" onclick="toggleCheckbox(); return false;" title="{{ trans('general.button.toggle-select') }}">
                                            <i class="fa fa-check-square-o"></i>
                                        </a>
                                    </th> -->
                                    <th>{{ trans('admin/customer-candidates/general.columns.name') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.type') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.phone') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.address') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.created') }}</th>
                                    <th>{{ trans('admin/customer-candidates/general.columns.actions') }}</th>
                                </tr>
                            </tfoot>
                            <tbody id="content">
                                @foreach($candidateFollowups as $cf)
                                <tr>
                                    <!-- <td align="center">
                                        {!! Form::checkbox('chkUser[]', $cf->id); !!}
                                    </td> -->
                                    <td>{!! link_to_route('admin.customer-candidates.show', $cf->customerCandidate->name, $cf->customerCandidate->id) !!}</td>
                                    <td>{{ Helpers::getCustomerTypeDisplayName($cf->customerCandidate->type) }}</td>
                                    <td>{{ $cf->customerCandidate->phone }}</td>
                                    <td>{{ $cf->customerCandidate->address }}</td>
                                    <td>{{ $cf->created_at }}</td>
                                    <td>
                                        <a href="{!! route('admin.candidate-followups.confirm-delete', $cf->id) !!}" data-toggle="modal" data-target="#modal_dialog" title="{{ trans('general.button.delete') }}"><i class="fa fa-trash-o deletable"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('body_bottom')
    <!-- Select2 4.0.0 -->
    <script src="{{ asset ("/bower_components/admin-lte/select2/js/select2.min.js") }}" type="text/javascript"></script>
    <!-- NProgress 0.2.0  -->
    <script src="{{ asset('/bower_components/nprogress/nprogress.js') }}" type="text/javascript"></script>
    <!-- DataTables -->
    <script src="{{ asset('/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function() {
            $(".select-candidate-type").select2();

            $('#example2').DataTable({
                "ordering": false
            });

            $('#search').click(function(event) {
                event.preventDefault();
                NProgress.start();
                var query = $(".select-candidate-type").val();
                var token = $(".select-candidate-type").attr('_token');

                $.ajax({
                    url      : "candidate-followups/select-by-type",
                    data     : ({query : query, _token : token}),
                    type     : 'POST',
                    dataType : 'html',
                    success: function(data){
                        $( "#content" ).empty();
                        $( "#content" ).append( data );
                        NProgress.done();
                    }
                });
            });
        });
    </script>
@endsection
