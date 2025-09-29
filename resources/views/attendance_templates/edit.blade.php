{{-- resources/views/attendance_templates/edit.blade.php --}}
<x-app-layout>
    <div class="mx-auto max-w-6xl">
        <div class="rounded-xl border bg-white shadow-sm">
            <!-- Card header -->
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-semibold">Edit Attendance Message Template</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Update your custom message for Arrival/Departure.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('attendance-templates.update', $tpl) }}"
                  x-data="tplForm(@js([
                        'type'    => old('type', $tpl->type),
                        'name'    => old('name', $tpl->name),
                        'channel' => old('channel', $tpl->channel),
                        'locale'  => old('locale', $tpl->locale),
                        'body'    => old('body', $tpl->body),
                  ]))"
                  class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Row: Type --}}
                <div class="grid grid-cols-12 items-center gap-4">
                    <label class="col-span-3 text-sm text-gray-600">Type</label>
                    <div class="col-span-9">
                        <select name="type" x-model="type" class="w-full rounded-md border-gray-300">
                            <option value="arrival">Arrival</option>
                            <option value="departure">Departure</option>
                        </select>
                        @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Row: Body --}}
                <div class="grid grid-cols-12 gap-4">
                    <label class="col-span-3 text-sm text-gray-600">Body</label>
                    <div class="col-span-9">
                        <textarea x-ref="body" name="body" x-model="body" rows="5"
                                  class="w-full rounded-md border-gray-300"
                                  placeholder="e.g. Dear Parent, {student_name} reached school at {arrival_time} on {date}."></textarea>
                        @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        <!-- Placeholder chips -->
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <template x-for="ph in placeholders" :key="ph">
                                <button type="button"
                                        class="rounded-md bg-green-100 px-2 py-1 font-semibold text-green-800 hover:bg-green-200"
                                        @click="insert(ph)">
                                    <span x-text="'{' + ph + '}'"></span>
                                </button>
                            </template>
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            Placeholders: {student_name}, {father_name}, {class_name}, {date_time}, {school_name}
                        </div>
                    </div>
                </div>

                {{-- Row: Active --}}
                <div class="grid grid-cols-12 items-center gap-4">
                    <label class="col-span-3 text-sm text-gray-600">Set as Active</label>
                    <div class="col-span-9">
                        {{-- Send 0 when unchecked so we can deactivate --}}
                        <input type="hidden" name="is_active" value="0">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1"
                                   class="h-4 w-4 rounded border-gray-300"
                                   @checked(old('is_active', $tpl->is_active))>
                            <span class="text-sm text-gray-700">Use this template for sending</span>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-black">
                        SAVE CHANGES
                    </button>

                    <button type="button"
                            class="rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                            @click="preview()">
                        Preview
                    </button>

                    <a href="{{ route('attendance-templates.index') }}"
                       class="ml-auto rounded-md border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                </div>

                {{-- Preview --}}
                <div>
                    <div class="text-sm text-gray-600 mb-1">Preview</div>
                    <textarea rows="6" class="w-100 rounded-lg border bg-gray-50 p-4" disabled x-text="previewText" name="" id=""></textarea>
                </div>
            </form>
        </div>
    </div>

    <script>
        function tplForm(initial = {}){
            return {
                type: initial.type ?? 'arrival',
                body: initial.body ?? '',
                previewText: '',
                placeholders: [
                    'student_name','father_name','class_name',
                    'date_time','school_name'
                ],
                insert(ph){
                    const ta = this.$refs.body;
                    const start = ta.selectionStart ?? this.body.length;
                    const end   = ta.selectionEnd ?? start;
                    const token = `{${ph}}`;
                    this.body = this.body.slice(0,start) + token + this.body.slice(end);
                    this.$nextTick(() => {
                        ta.focus();
                        ta.selectionStart = ta.selectionEnd = start + token.length;
                    });
                },
                async preview(){
                    const res = await fetch('{{ route("attendance-templates.preview") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}','Content-Type':'application/json' },
                        body: JSON.stringify({
                            body: this.body,
                            sample: {
                                student_name: 'Ali Khan',
                                father_name: 'Mr. Khan',
                                class_name: 'Grade 5',
                                section: 'B',
                                arrival_time: '07:58 AM',
                                departure_time: '01:40 PM',
                                date_time: '{{ now()->format("d M Y") }}' + ' 07:58 AM',
                                school_name: 'ABC School'
                            }
                        })
                    });
                    const json = await res.json();
                    this.previewText = json.message ?? '';
                }
            }
        }
    </script>
</x-app-layout>
