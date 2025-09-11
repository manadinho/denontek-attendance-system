<x-app-layout>
    <x-slot name="header">
		<div class="row">
			<h2 class="col-md-9 font-semibold text-xl text-gray-800 leading-tight">
				{{ __('Import') }}
			</h2>
			<div class="col-md-3">
				<select name="download-sample" class="form-control" onchange="downloadFile(this)">
					<option value="">Download Sample File</option>
					<option value="{{asset('assets/sample-import-files/student.xlsx')}}">Students</option>
					<option value="{{asset('assets/sample-import-files/staff.xlsx')}}">Staff</option>
				</select>
			</div>
		</div>
    </x-slot>

    <div class="mt-3">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label for="school_id">School</label>
                        <select name="school_id" id="school_id" class="form-control" required>
                            <option value="">Select School</option>
                            @forelse($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="type">Import Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="">Select Import Type</option>
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="formFile" class="form-label">Excel File</label>
                        <input class="form-control" type="file" id="formFile" name="file" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark mt-3">Upload</button>
            </form>
        </div>
        
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>School</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total Records</th>
                        <th>Good Records</th>
                        <th>Bad Records</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fileUploads as $fileUpload)
                        <tr>
                            <td>{{ getUploadFileName($fileUpload->file_name) }}</td>
                            <td>{{ $fileUpload->school->name }}</td>
                            <td>{{ ucwords($fileUpload->type) }}</td>
                            <td>{{ ucwords($fileUpload->status) }}</td>
                            <td>{{ $fileUpload->total_records }}</td>
                            <td>{{ $fileUpload->good }}</td>
                            <td>{{ $fileUpload->cannot_upload }}</td>
                            <td>
                                @if($fileUpload->cannot_upload)
                                    <a href="{{ route('imports.show-failed', $fileUpload->id) }}" title="Failed Records"><i class="fas fa-exclamation-triangle"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No Files Uploaded Yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $fileUploads->links() }}
        </div>
    </div>
</x-app-layout>

<script>
	function downloadFile(select) {
		if (select.value) {
			window.location.href = select.value;
			select.value = ""; // reset dropdown
		}
	}
</script>