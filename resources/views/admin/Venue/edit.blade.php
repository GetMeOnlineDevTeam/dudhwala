@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<link href="{{ asset('assets/plugins/fancy-file-uploader/fancy_fileupload.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.css') }}" rel="stylesheet">

<style>
  .img-preview{width:80px;height:80px;object-fit:cover;margin-right:8px}
  .custom-upload-box{width:100%;display:flex;flex-direction:column;justify-content:center;align-items:center;min-height:200px;border:2px dashed #cfcfcf;border-radius:6px;background:#fdfdfd;cursor:pointer;transition:background-color .2s}
  .custom-upload-box:hover{background:#f8f8f8}
  #previewContainer .image-preview,#bannerContainer .image-preview{position:relative;width:auto;max-width:150px}
  #previewContainer img,#bannerContainer img{width:100%;height:auto;border-radius:4px;border:1px solid #ddd;box-shadow:0 1px 3px rgba(0,0,0,.1);display:block}
  .remove-btn{position:absolute;top:-8px;right:-1px;background:#dc3545;border:none;color:#fff;border-radius:50%;width:22px;height:22px;font-size:14px;line-height:18px;padding:0;display:flex;justify-content:center;align-items:center;cursor:pointer}
  .section{padding:20px;margin-bottom:30px;border:1px solid #ddd;border-radius:8px;background:#f9f9f9}
  .section h5{margin-bottom:15px;font-weight:700}
  #thumbnailContainer img{height:70px;width:auto;border-radius:5px}
  .time-slot-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.06);padding:20px;margin-bottom:16px}
  .delete-tag{position:absolute;top:6px;right:6px;background:#dc3545;color:#fff;border:none;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer}
  .marked-for-delete{opacity:.35;filter:grayscale(.2)}
  .btn-icon{display:inline-flex;align-items:center;gap:8px}
</style>
@endsection

@section('title','Edit Venue')

@section('content')
<div class="main-content">
  <div class="container py-4">
    <h2 class="mb-4">Edit Venue — {{ $venue->name }}</h2>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <strong>There were some problems with your submission:</strong>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div id="venue-stepper" class="bs-stepper">
      {{-- Header --}}
      <div class="bs-stepper-header" role="tablist">
        @php
          $tabs = [
            1 => ['Venue Details','Name & Info'],
            2 => ['Address','Location Data'],
            3 => ['Images','Cover & Gallery'],
            4 => ['Time Slots','Edit Slots'],
          ];
        @endphp
        @foreach($tabs as $i=>$t)
          <div class="step" data-target="#pane-{{ $i }}">
            <button class="step-trigger" role="tab" id="trigger-{{ $i }}" aria-controls="pane-{{ $i }}">
              <span class="bs-stepper-circle">{{ $i }}</span>
              <div class="bs-stepper-label">
                <div class="bs-stepper-title">{{ $t[0] }}</div>
                <div class="bs-stepper-subtitle">{{ $t[1] }}</div>
              </div>
            </button>
          </div>
          @if($i<count($tabs)) <div class="bs-stepper-line"></div> @endif
        @endforeach
      </div>

      {{-- Content --}}
      <div class="bs-stepper-content border p-4">
        <form method="POST" action="{{ route('admin.venues.update', $venue) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Pane 1: Venue --}}
          <div id="pane-1" class="bs-stepper-pane" role="tabpanel" aria-labelledby="trigger-1">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$venue->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Total Floors</label>
                <input name="total_floor" type="number" min="0" class="form-control @error('total_floor') is-invalid @enderror" value="{{ old('total_floor',$venue->total_floor) }}">
                @error('total_floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">About</label>
                <textarea name="about" class="form-control @error('about') is-invalid @enderror" rows="3">{{ old('about',$venue->about) }}</textarea>
                @error('about')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Amenities</label>
                <input name="amenities" type="text" class="form-control @error('amenities') is-invalid @enderror" value="{{ old('amenities',$venue->amenities) }}">
                @error('amenities')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6 form-check">
                <input name="multi_floor" type="checkbox" id="multi_floor" class="form-check-input" value="1" {{ old('multi_floor',$venue->multi_floor) ? 'checked' : '' }}>
                <label for="multi_floor" class="form-check-label">Supports Multiple Floors?</label>
              </div>
              <div class="col-12">
                <button type="button" class="btn btn-primary" onclick="venueStepper.next()">Next &raquo;</button>
              </div>
            </div>
          </div>

          {{-- Pane 2: Address --}}
          <div id="pane-2" class="bs-stepper-pane" role="tabpanel" aria-labelledby="trigger-2">
            <div class="row g-3">
              @php $addr = old('address', optional($venue->address)->toArray() ?? []); @endphp
              <div class="col-md-4">
                <label class="form-label">City</label>
                <input name="address[city]" type="text" class="form-control @error('address.city') is-invalid @enderror" value="{{ $addr['city'] ?? '' }}">
                @error('address.city')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">State</label>
                <input name="address[state]" type="text" class="form-control @error('address.state') is-invalid @enderror" value="{{ $addr['state'] ?? '' }}">
                @error('address.state')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Pincode</label>
                <input name="address[pincode]" type="text" class="form-control @error('address.pincode') is-invalid @enderror" value="{{ $addr['pincode'] ?? '' }}">
                @error('address.pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Address</label>
                <textarea name="address[addr]" class="form-control @error('address.addr') is-invalid @enderror" rows="2">{{ $addr['addr'] ?? '' }}</textarea>
                @error('address.addr')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Google Maps Link</label>
                <input name="address[google_link]" type="url" class="form-control @error('address.google_link') is-invalid @enderror" value="{{ $addr['google_link'] ?? '' }}">
                @error('address.google_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="venueStepper.previous()">&laquo; Back</button>
                <button type="button" class="btn btn-primary" onclick="venueStepper.next()">Next &raquo;</button>
              </div>
            </div>
          </div>

          {{-- Pane 3: Images --}}
          <div id="pane-3" class="bs-stepper-pane" role="tabpanel" aria-labelledby="trigger-3">
            <div class="row">
              <div class="col-xl-9 mx-auto">
                {{-- Cover --}}
                <div class="mb-4">
                  <label class="form-label d-block">Cover Image</label>
                  <div class="custom-upload-box p-3">
                    @php $cover = $venue->images->firstWhere('is_cover', true); @endphp
                    <div id="coverPreview" class="image-preview mb-3" style="max-width:150px">
                      @if($cover)
                        <img src="{{ asset('storage/'.$cover->image) }}?v={{ time() }}" alt="Cover Image" class="img-thumbnail"/>
                        <input type="hidden" name="existing_cover_id" value="{{ $cover->id }}">
                      @else
                        <img src="https://placehold.co/300x200?text=No+Cover" alt="Cover Image" class="img-thumbnail"/>
                      @endif
                    </div>
                    {{-- Selecting a file will set replace_cover=1 (used by controller to swap files) --}}
                    <input type="hidden" name="replace_cover" id="replace_cover" value="0">
                    <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*" onchange="updateCoverImagePreview(this)">
                  </div>
                  @error('cover_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Gallery --}}
                <h6 class="text-uppercase">Gallery Images</h6>
                <div class="custom-upload-box p-3">
                  <div class="d-flex flex-wrap mb-3" id="previewContainer">
                    @foreach($venue->images as $image)
                      @if (!$image->is_cover)
                        <div class="me-3 mb-3 position-relative gallery-existing" data-image-id="{{ $image->id }}">
                          <img src="{{ asset('storage/'.$image->image) }}?v={{ time() }}" alt="Gallery Image" class="img-thumbnail img-preview" style="width:150px;height:150px;object-fit:cover;">
                          <button type="button" class="delete-tag" title="Remove" onclick="toggleDeleteExisting(this, {{ $image->id }})">
                            <span class="material-icons-outlined" style="font-size:16px">close</span>
                          </button>
                        </div>
                      @endif
                    @endforeach
                  </div>

                  {{-- ids to delete --}}
                  <div id="deleteImageInputs"></div>

                  {{-- add new --}}
                  <label class="form-label mt-2">Upload Gallery Images</label>
                  <input type="file" name="gallery_images[]" class="form-control @error('gallery_images') is-invalid @enderror" accept="image/*" multiple onchange="updateGalleryImagePreviews(this)">
                </div>
                @error('gallery_images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('gallery_images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <div class="mt-3 d-flex justify-content-between">
                  <button type="button" class="btn btn-secondary" onclick="venueStepper.previous()">Back</button>
                  <button type="button" class="btn btn-primary" onclick="venueStepper.next()">Next</button>
                </div>
              </div>
            </div>
          </div>

          {{-- Pane 4: Time Slots (with Deposit) --}}
          <div id="pane-4" class="bs-stepper-pane" role="tabpanel" aria-labelledby="trigger-4">
            <div class="row g-3">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Time Slots</h5>
                <button type="button" class="btn btn-outline-primary btn-icon" id="addTimeSlotBtn">
                  <span class="material-icons-outlined" style="font-size:18px;line-height:0">add</span>
                  Add Slot
                </button>
              </div>

              <div id="timeSlotsContainer" class="col-12">
                @foreach($venue->timeSlots as $i => $slot)
                  @php $ts = old("timeslots.$i", $slot->toArray()); @endphp
                  <div class="time-slot-card position-relative" data-slot-index="{{ $i }}">
                    <input type="hidden" name="timeslots[{{ $i }}][id]" value="{{ $slot->id }}">

                    <label class="form-label">Name</label>
                    <input type="text" name="timeslots[{{ $i }}][name]" class="form-control @error('timeslots.'.$i.'.name') is-invalid @enderror" value="{{ $ts['name'] ?? '' }}" placeholder="Enter time slot name">
                    @error("timeslots.$i.name")<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <div class="row g-3 mt-1">
                      <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="timeslots[{{ $i }}][start_time]" class="form-control @error('timeslots.'.$i.'.start_time') is-invalid @enderror" value="{{ \Illuminate\Support\Str::of($ts['start_time'] ?? '')->limit(5,'') }}">
                        @error("timeslots.$i.start_time")<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="timeslots[{{ $i }}][end_time]" class="form-control @error('timeslots.'.$i.'.end_time') is-invalid @enderror" value="{{ \Illuminate\Support\Str::of($ts['end_time'] ?? '')->limit(5,'') }}">
                        @error("timeslots.$i.end_time")<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input type="number" min="0" name="timeslots[{{ $i }}][price]" class="form-control @error('timeslots.'.$i.'.price') is-invalid @enderror" value="{{ $ts['price'] ?? '' }}">
                        @error("timeslots.$i.price")<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Deposit Amount</label>
                        <input type="number" min="0" name="timeslots[{{ $i }}][deposit_amount]" class="form-control @error('timeslots.'.$i.'.deposit_amount') is-invalid @enderror" value="{{ $ts['deposit_amount'] ?? '' }}" placeholder="e.g. 2000">
                        @error("timeslots.$i.deposit_amount")<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
                    </div>

                    <div class="d-flex gap-4 mt-3">
                      <div class="form-check">
                        <input type="hidden" name="timeslots[{{ $i }}][single_time]" value="0">
                        <input name="timeslots[{{ $i }}][single_time]" type="checkbox" class="form-check-input" value="1" {{ !empty($ts['single_time']) ? 'checked' : '' }}>
                        <label class="form-check-label">Single Time</label>
                      </div>
                      <div class="form-check">
                        <input type="hidden" name="timeslots[{{ $i }}][full_time]" value="0">
                        <input name="timeslots[{{ $i }}][full_time]" type="checkbox" class="form-check-input" value="1" {{ !empty($ts['full_time']) ? 'checked' : '' }}>
                        <label class="form-check-label">Full Time</label>
                      </div>
                      <div class="form-check">
                        <input type="hidden" name="timeslots[{{ $i }}][full_venue]" value="0">
                        <input name="timeslots[{{ $i }}][full_venue]" type="checkbox" class="form-check-input" value="1" {{ !empty($ts['full_venue']) ? 'checked' : '' }}>
                        <label class="form-check-label">Full Venue Booking</label>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="venueStepper.previous()">&laquo; Back</button>
                <button type="submit" class="btn btn-success">Save Changes</button>
              </div>
            </div>
          </div>

        </form>
      </div> {{-- /.bs-stepper-content --}}
    </div> {{-- /#venue-stepper --}}
  </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="{{ asset('assets/plugins/fancy-file-uploader/jquery.ui.widget.js') }}"></script>
<script src="{{ asset('assets/plugins/fancy-file-uploader/jquery.fileupload.js') }}"></script>
<script src="{{ asset('assets/plugins/fancy-file-uploader/jquery.iframe-transport.js') }}"></script>
<script src="{{ asset('assets/plugins/fancy-file-uploader/jquery.fancy-fileupload.js') }}"></script>
<script src="{{ asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  window.venueStepper = new Stepper(document.querySelector('#venue-stepper'), { linear:false });

  $('#fancy-file-upload').FancyFileUpload?.({ params:{action:'fileuploader'}, maxfilesize:1000000 });
  $('#image-uploadify').imageuploadify?.({ maxFiles:6 });

  // dynamic slot add on edit page too
  window.slotIndex = document.querySelectorAll('#timeSlotsContainer .time-slot-card').length || 0;
  const addBtn = document.getElementById('addTimeSlotBtn');
  if (addBtn) addBtn.addEventListener('click', addTimeSlot);
});

function updateCoverImagePreview(input){
  if(!input.files?.length) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    const img = document.querySelector('#coverPreview img');
    if(img) img.src = e.target.result;
    // mark that user wants to replace cover
    const rc = document.getElementById('replace_cover'); if (rc) rc.value = '1';
  };
  reader.readAsDataURL(input.files[0]);
}

function updateGalleryImagePreviews(input){
  const preview = document.getElementById('previewContainer');
  const files = Array.from(input.files || []);
  files.forEach(file=>{
    const reader = new FileReader();
    reader.onload = e=>{
      const wrap = document.createElement('div');
      wrap.className = 'me-3 mb-3 position-relative';
      wrap.innerHTML = `<img src="${e.target.result}" class="img-thumbnail img-preview" style="width:150px;height:150px;object-fit:cover;" alt="New Gallery">`;
      preview.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
}

// mark/unmark existing gallery image for deletion
function toggleDeleteExisting(btn, id){
  const card = btn.closest('.gallery-existing');
  const container = document.getElementById('deleteImageInputs');
  const inputName = 'delete_image_ids[]';
  const selector = `input[type="hidden"][name="${inputName}"][value="${id}"]`;

  const existing = container.querySelector(selector);
  if(existing){
    existing.remove();
    card.classList.remove('marked-for-delete');
    btn.title = 'Remove';
  }else{
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = inputName;
    hidden.value = id;
    container.appendChild(hidden);
    card.classList.add('marked-for-delete');
    btn.title = 'Undo';
  }
}

function addTimeSlot(){
  const idx = window.slotIndex++;
  const container = document.getElementById('timeSlotsContainer');
  const card = document.createElement('div');
  card.className = 'time-slot-card position-relative';
  card.setAttribute('data-slot-index', idx);
  card.innerHTML = `
    <button type="button" class="remove-btn" title="Remove" onclick="this.closest('.time-slot-card')?.remove()">
      <span class="material-icons-outlined">delete</span>
    </button>

    <label class="form-label">Name</label>
    <input type="text" name="timeslots[${idx}][name]" class="form-control" placeholder="Enter time slot name" />

    <div class="row g-3 mt-1">
      <div class="col-md-3">
        <label class="form-label">Start Time</label>
        <input type="time" name="timeslots[${idx}][start_time]" class="form-control" />
      </div>
      <div class="col-md-3">
        <label class="form-label">End Time</label>
        <input type="time" name="timeslots[${idx}][end_time]" class="form-control" />
      </div>
      <div class="col-md-3">
        <label class="form-label">Price</label>
        <input type="number" min="0" name="timeslots[${idx}][price]" class="form-control" />
      </div>
      <div class="col-md-3">
        <label class="form-label">Deposit Amount</label>
        <input type="number" min="0" name="timeslots[${idx}][deposit_amount]" class="form-control" placeholder="e.g. 2000" />
      </div>
    </div>

    <div class="d-flex gap-4 mt-3">
      <div class="form-check">
        <input type="hidden" name="timeslots[${idx}][single_time]" value="0">
        <input name="timeslots[${idx}][single_time]" type="checkbox" class="form-check-input" value="1">
        <label class="form-check-label">Single Time</label>
      </div>
      <div class="form-check">
        <input type="hidden" name="timeslots[${idx}][full_time]" value="0">
        <input name="timeslots[${idx}][full_time]" type="checkbox" class="form-check-input" value="1">
        <label class="form-check-label">Full Time</label>
      </div>
      <div class="form-check">
        <input type="hidden" name="timeslots[${idx}][full_venue]" value="0">
        <input name="timeslots[${idx}][full_venue]" type="checkbox" class="form-check-input" value="1">
        <label class="form-check-label">Full Venue Booking</label>
      </div>
    </div>
  `;
  container.appendChild(card);
}
</script>
@endsection
