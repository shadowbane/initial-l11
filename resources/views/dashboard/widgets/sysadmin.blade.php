<div class="card py-2">
    <div class="card-body">
        <div class="text-uppercase">
            <h3><i class="la la-cog"></i> Admin Control Panel</h3>
        </div>
        <div class="d-md-flex d-grid justify-content-lg--between flex-lg-row flex-wrap gap-4">
            <div>
                <button id="clear-cache-button"
                        class="btn justify-content-center btn-info flex-grow-1 w-100 sysadminActionButton"
                        type="button"
                        data-action="clear-cache"
                >
                    <i class="las la-broom la-2x mx-1"></i>
                    <span class="ladda-label" style="display: block">Clear Cache</span>
                </button>
            </div>
            <div>
                <button id="new-backup-button"
                        onclick="window.location.href='{{ url('/backup') }}'"
                        class="btn justify-content-center btn-danger flex-grow-1 w-100"
                        type="button"
                >
                    <i class="las la-save la-2x mx-1"></i>
                    <span class="ladda-label" style="display: block">Backup</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
    <script>
        function callAction($action) {
            fetch("{{ url('/widget/sysadmin') }}/" + $action, {
                method: "post",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
            }).then(system.status).then(system.json).then(data => {
                swal({
                    title: data.data.message,
                    icon: 'success',
                });
            }).catch(system.error);
        }

        $(document).ready(function () {
            document.querySelectorAll('.sysadminActionButton').forEach($button => {
                $button.addEventListener('click', ($event) => {
                    $event.preventDefault();
                    callAction($button.getAttribute('data-action'));
                });
            });
        });
    </script>
@endpush
