<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Alerts
        </h2>
    </x-slot>

    <div
        x-data="alertsPage()"
        class="py-8"
    >
        <div>
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="table-responsive mt-3">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3">Admin Contacts</th>
                            <th class="px-4 py-3">Active</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($alerts as $alert)
                            <tr>
                                <td>
                                    <div class="text-sm font-medium text-gray-900">{{ $alert->title }}</div>
                                </td>
                                <td>
                                    {{ $alert->time }}
                                </td>
                                <td>
                                    <div class="max-w-lg truncate" title="{{ $alert->admin_contacts }}">
                                        @forelse(json_decode($alert->admin_contacts, true) as $admin_contact)
                                            <span class="inline-block badge bg-secondary">
                                                {{ $admin_contact }}
                                            </span>
                                        @empty
                                            No contacts
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if($alert->active)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-500 px-2.5 py-0.5 text-xs font-medium text-white">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:void(0)"
                                        @click="openEdit(@js($alert))"
                                    >
                                    <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No alerts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $alerts->links() }}
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center"
            aria-modal="true"
            role="dialog"
        >
            <div class="fixed inset-0 bg-gray-900/50" @click="close()"></div>

            <div class="relative w-full max-w-2xl rounded-lg bg-white shadow-lg">
                <div class="flex items-center justify-between px-6 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Alert</h3>
                </div>

                <form
                    method="POST"
                    :action="updateAction"
                    class="px-6 space-y-5"
                >
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" x-model="form.id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" x-model="form.title" disabled>
                        <p class="mt-1 text-xs text-gray-500">Title can’t be edited here.</p>
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700">Time</label>
                        <select name="time" id="time" x-model="form.time" class="mt-1 block w-100 rounded-md border-gray-300 shadow-sm">
                            @php $selected = old('time', $currentTime ?? null); @endphp
                            @for ($h = 0; $h < 24; $h++)
                                @for ($m = 0; $m < 60; $m += 30)
                                    @php $val = sprintf('%02d:%02d', $h, $m); @endphp
                                    <option value="{{ $val }}">
                                        {{ $val }}
                                    </option>
                                @endfor
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Admin Contacts</label>
                        <select x-model="form.admin_contacts" name="admin_contacts[]" id="admin_contacts" class="form-control rounded" multiple="multiple">
                            @forelse($adminPhoneNumbers as $adminPhoneNumber)
                                <option value="{{ $adminPhoneNumber }}">{{ $adminPhoneNumber }}</option>
                            @empty
                                <option value="">No Admin phone found</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="active" name="active" type="checkbox" class="rounded border-gray-300" :checked="form.active">
                        <label for="active" class="text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-4 mb-4">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" @click="close()">Cancel</button>
                        <button type="submit" class="btn btn-dark ">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script>
        function alertsPage() {
            return {
                showModal: false,
                updateAction: '',
                form: { id: null, title: '', time: '', admin_contacts: '', active: false },

                openEdit(alert) {
                    admin_contacts = JSON.parse(alert.admin_contacts);
                    this.form = {
                        id: alert.id,
                        title: alert.title ?? '',
                        time: alert.time ?? '',
                        admin_contacts: admin_contacts ?? '',
                        active: !!alert.active,
                    };
                    $('#admin_contacts').val(admin_contacts).trigger('change');
                    this.updateAction = `{{ route('admin-alerts.update') }}`;
                    this.showModal = true;

                    // sync checkbox (because :checked is not two-way)
                    this.$nextTick(() => {
                        const el = document.querySelector('input[name="active"]');
                        if (el) el.checked = !!this.form.active;
                    });
                },
                close() {
                    this.showModal = false;
                },
            }
        }

        $('#admin_contacts').select2();
    </script>
    
</x-app-layout>
