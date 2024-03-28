<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class>Request Detail</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-responsive-md table-hover">
                            <tr>
                                <th>IP Address</th>
                                <td>{{ $data->ip }}</td>
                            </tr>
                            <tr>
                                <th>Ajax</th>
                                <td>{{ $data->request_detail['ajax'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>JSON</th>
                                <td>{{ $data->request_detail['isJson'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Method</th>
                                <td>{{ $data->request_detail['method'] }}</td>
                            </tr>
                            <tr>
                                <th>URL</th>
                                <td>{{ $data->request_detail['url'] }}</td>
                            </tr>
                            <tr>
                                <th>Query Data</th>
                                <td>{{ collect($data->request_detail['query']) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Browser Detail</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-responsive-sm table-sm">
                            <tr>
                                <th>Mobile</th>
                                <td>{{ $data->browser_detail['isMobile'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Tablet</th>
                                <td>{{ $data->browser_detail['isTablet'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Desktop</th>
                                <td>{{ $data->browser_detail['isDesktop'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Bot</th>
                                <td>{{ $data->browser_detail['isBot'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Browser Family</th>
                                <td>{{ $data->browser_detail['browserFamily'] }}</td>
                            </tr>
                            <tr>
                                <th>Browser Version</th>
                                <td>{{ $data->browser_detail['browserVersion'] }}</td>
                            </tr>
                            <tr>
                                <th>OS Family</th>
                                <td>{{ $data->browser_detail['osFamily'] }}</td>
                            </tr>
                            <tr>
                                <th>OS Version</th>
                                <td>{{ $data->browser_detail['osVersion'] }}</td>
                            </tr>
                            <tr>
                                <th>Device Family</th>
                                <td>{{ $data->browser_detail['deviceFamily'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="padding: 0 1px 0 1px;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Causer & Subject</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-responsive-sm table-sm">
                            <tr>
                                <th>Causer</th>
                                <td>{{ $data->causer_type }}</td>
                            </tr>
                            <tr>
                                <th>Causer ID</th>
                                <td>{{ $data->causer_id }}</td>
                            </tr>
                            <tr>
                                <th>Subject</th>
                                <td>{{ $data->subject_type }}</td>
                            </tr>
                            <tr>
                                <th>Subject ID</th>
                                <td>{{ $data->subject_id }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(count($data->properties))
        <div class="row px-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Properties</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive-sm table-hover">
                            @foreach ($data->properties as $key => $props)
                                @if ($key == 'attributes')
                                    @include('layouts.activity-logs._properties')
                                @elseif ($key == 'old')
                                    @include('layouts.activity-logs._properties')
                                @else
                                    @include('layouts.activity-logs._properties')
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
