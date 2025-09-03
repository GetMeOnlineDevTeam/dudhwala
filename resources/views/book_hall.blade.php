<x-app-layout>
    <style>
        .slot-unavailable{background:#F3F4F6!important;color:#9CA3AF!important;border:2px solid #E5E7EB!important;font-weight:600;cursor:not-allowed!important;opacity:1}
        .slot-selected{background:#15803d!important;color:#fff!important;border:2px solid #15803d!important;font-weight:600}
        .slot-available{background:#fff!important;color:#15803d!important;border:2px solid #16a34a!important;font-weight:600}
        [x-cloak]{display:none!important}
        .animate-fade-in{animation:fadeIn .2s}
        @keyframes fadeIn{from{opacity:0;transform:scale(.96)}to{opacity:1}}
        .spinner{width:24px;height:24px;border:3px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s ease-in-out infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;border-radius:9999px;padding:4px 10px}
        .pill-refund{background:#ecfdf5;color:#065f46;border:1px solid #34d399}
        .muted{color:#6b7280}
        .notice{background:#fff7ed;border:1px solid #fed7aa;color:#92400e;border-radius:12px;padding:12px 14px}
        .divider{height:1px;background:#e5e7eb;margin:12px 0}
    </style>

    {{-- keep your original JS, only adding discount wiring --}}
    <script>
  window.csrfToken   = @json(csrf_token());
  window.razorpayKey = "{{ config('services.razorpay.key') }}";
  window.dudhwalaDiscountFromServer = @json($dudhwalaDiscount ?? 0);

  function computeDiscountFromRule(rentOnly, raw){
      const s = String(raw ?? '').trim();
      if (!s) return 0;
      const n = Number(s.replace('%','').trim());
      if (!isFinite(n) || n <= 0) return 0;
      const isPercent = s.includes('%') || n <= 100;
      return Math.round(isPercent ? (rentOnly * n)/100 : n);
  }

  function bookingForm(isVerified, bookingSuccess, invoiceUrl){
    return {
      /* state */
      step: bookingSuccess ? 3 : 1,
      uploadName: 'Upload documents (Aadhar Card)',
      documentType: '',
      documentFile: null,
      termsAccepted: false,
      selectedVenueId: '',
      selectedDate: '',
      slots: [],
      selectedSlots: [],
      venueName: '',
      errors: {},
      invoiceUrl: invoiceUrl || '',
      isLoading: false,

      /* community + discount config */
      community: 'non-dudhwala',
      dudhwalaDiscount: window.dudhwalaDiscountFromServer,

      /* totals */
      get totalRent(){ return this.selectedSlots.reduce((s,x)=> s + Number(x.price||0), 0); },
      get totalDeposit(){ return this.selectedSlots.reduce((s,x)=> s + Number(x.deposit||0), 0); },
      get grossPayable(){ return this.totalRent + this.totalDeposit; },
      get appliedDiscount(){
        if (this.community !== 'dudhwala') return 0;
        return computeDiscountFromRule(this.totalRent, this.dudhwalaDiscount);
      },
      get netPayable(){
        const net = this.grossPayable - this.appliedDiscount;
        return net > 0 ? net : 0;
      },

      money(n){ return Number(n || 0).toLocaleString('en-IN'); },

      updateVenue(){
        this.selectedSlots = [];
        this.slots = [];
        this.venueName = this.$refs[`venue-${this.selectedVenueId}`]?.textContent || '';
      },

      onDateChange(){
        this.selectedSlots = [];
        this.slots = [];
        delete this.errors.selectedSlots;
        if (this.selectedVenueId && this.selectedDate) this.fetchSlots();
      },

      fetchSlots(){
        fetch(`/book_hall/slots/${this.selectedVenueId}/${this.selectedDate}`)
          .then(res => res.json())
          .then(data => {
            this.slots = (data || []).map(s => ({
              ...s,
              deposit: Number(s.deposit ?? s.deposit_amount ?? 0),
              price: Number(s.price ?? 0)
            }));
          })
          .catch(err => console.error('Error fetching slots:', err));
      },

      selectSingleSlot(slot){ this.selectedSlots = [slot]; },
      isSlotSelected(slot){ return this.selectedSlots.some(s => s.slot_id === slot.slot_id); },

      handleFileChange(e){
        if (isVerified) return;
        const file = e.target.files[0];
        this.uploadName = file ? file.name : 'Upload documents (Aadhar Card)';
        this.documentFile = file;
      },

      validateForm(){
        this.errors = {};
        if (!this.selectedVenueId) this.errors.venue = 'Please select a venue.';
        if (!isVerified){
          if (!this.documentType) this.errors.documentType = 'Please select a document.';
          if (!this.documentFile) this.errors.documentFile = 'Please upload your document.';
        }
        if (!this.selectedDate){
          this.errors.selectedDate = 'Please select a date.';
        }else{
          const today = new Date(), sel = new Date(this.selectedDate);
          today.setHours(0,0,0,0); sel.setHours(0,0,0,0);
          if (sel <= today) this.errors.selectedDate = 'Date must be after today.';
        }
        if (this.selectedSlots.length === 0) this.errors.selectedSlots = 'Please select at least one time slot.';
        if (!this.termsAccepted) this.errors.termsAccepted = 'You must accept the terms.';

        if (Object.keys(this.errors).length === 0){
          this.step = 2;
          this.$nextTick(()=> this.$refs.bookingSummary.scrollIntoView({behavior:'smooth', block:'center'}));
        }
      },

      /* ONLINE ONLY: call Razorpay directly */
      makePayment(){
        this.isLoading = true;
        this.initiateRazorpayPayment();
      },

      async initiateRazorpayPayment(){
        try{
          const response = await fetch('/razorpay/order', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken},
            body: JSON.stringify({
              amount: Math.round(this.netPayable * 100),
              payment_mode: 'online' // forced online
            })
          });
          if (!response.ok) throw new Error('Failed to create payment order');
          const orderData = await response.json();
          if (!orderData.success) throw new Error(orderData.message || 'Payment order creation failed');

          const options = {
            key: window.razorpayKey,
            amount: orderData.amount,
            currency: orderData.currency,
            order_id: orderData.order_id,
            name: "{{ config('app.name') }}",
            description: "Hall Booking Payment",
            handler: (resp) => this.handlePaymentSuccess(resp, orderData.order_id),
            prefill: {
              name: "{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}",
              contact: "{{ auth()->user()->contact_number }}"
            },
            theme: { color: "#116631" },
            modal: { ondismiss: () => this.isLoading = false }
          };

          const rzp = new Razorpay(options);
          rzp.open();
        }catch(error){
          console.error('Payment Error:', error);
          this.isLoading = false;
          Swal.fire({ icon:'error', title:'Payment Error', text:'Failed to initialize payment. Please try again.', confirmButtonColor:'#d33' });
        }
      },

      handlePaymentSuccess(response, orderId){
        const p = document.createElement('input'); p.type='hidden'; p.name='razorpay_payment_id'; p.value=response.razorpay_payment_id;
        const o = document.createElement('input'); o.type='hidden'; o.name='razorpay_order_id';  o.value=orderId;
        this.$refs.bookingForm.appendChild(p); this.$refs.bookingForm.appendChild(o);
        // ensure payment_method is online
        const method = this.$refs.bookingForm.querySelector('input[name="payment_method"]');
        if (method) method.value = 'online';
        this.$refs.bookingForm.submit();
      }
    }
  }
</script>

    <div class="max-w-4xl mx-auto py-12 px-6"
         x-data="bookingForm({{ auth()->user()->is_verified ? 'true' : 'false' }}, {{ session('success') ? 'true' : 'false' }}, '{{ session('invoice_url') }}')">

        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">Schedule Your Event</h1>
        <p class="text-center text-gray-600 mb-10 text-sm">
            Booking can be set for after 1 day as it takes <strong>24 Hours</strong> to process the request
        </p>

        <!-- STEP 1 -->
        <form x-show="step === 1" @submit.prevent="validateForm" x-ref="bookingForm"
              class="bg-white border border-gray-200 rounded-xl shadow px-10 py-10 space-y-8"
              method="POST" action="{{ route('book.complete') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Community -->
                <div class="md:col-span-2 text-center">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Select Community</label>
                    <select class="inline-block w-1/2 border border-gray-300 rounded-md p-2 bg-gray-100"
                            name="community" id="community" x-model="community">
                        <option value="non-dudhwala">Non Dudhwala</option>
                        <option value="dudhwala">Dudhwala</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Name</label>
                    <input type="text" readonly value="{{ auth()->user()->first_name }}"
                           class="w-full border border-gray-300 rounded-md p-2 bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Surname</label>
                    <input type="text" readonly value="{{ auth()->user()->last_name }}"
                           class="w-full border border-gray-300 rounded-md p-2 bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Mobile Number</label>
                    <input type="text" readonly value="{{ auth()->user()->contact_number }}"
                           class="w-full border border-gray-300 rounded-md p-2 bg-gray-100">
                </div>

                <!-- Venue -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Select Venue</label>
                    <select id="venue_id" x-model="selectedVenueId" @change="updateVenue()"
                            class="w-full border border-gray-300 rounded-md p-2">
                        <option disabled value="">Select Venue</option>
                        @foreach ($venues as $v)
                            <option value="{{ $v->id }}" x-ref="venue-{{ $v->id }}" data-multifloor="{{ $v->multi_floor ? 1 : 0 }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.venue"><p class="text-xs text-red-600 mt-1" x-text="errors.venue"></p></template>
                </div>

                @if (!auth()->user()->is_verified)
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Select Document Type <span class="text-red-600">*</span></label>
                        <select x-model="documentType" name="document_type" class="w-full border border-gray-300 rounded-md p-2">
                            <option disabled value="">Select Document</option>
                            <option value="Aadhar Card">Aadhar Card</option>
                            <option value="Driving License">Driving License</option>
                            <option value="Voter ID">Voter ID</option>
                        </select>
                        <template x-if="errors.documentType"><p class="text-xs text-red-600 mt-1" x-text="errors.documentType"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Upload Documents <span class="text-red-600">*</span></label>
                        <input type="file" id="upload_docs" class="sr-only" name="document_file" @change="handleFileChange($event)">
                        <label for="upload_docs" class="flex items-center h-12 border border-gray-300 rounded-lg px-3 bg-white cursor-pointer hover:border-gray-400 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 2h6l6 6v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 2v6h6" />
                            </svg>
                            <span class="ml-3 text-gray-500 text-sm" x-text="uploadName"></span>
                        </label>
                        <template x-if="errors.documentFile"><p class="text-xs text-red-600 mt-1" x-text="errors.documentFile"></p></template>
                    </div>
                @endif

                <!-- Date -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Date <span class="text-red-600">*</span></label>
                    <input type="date" x-model="selectedDate" name="booking_date" @change="onDateChange" class="w-full border border-gray-300 rounded-md p-2">
                    <template x-if="errors.selectedDate"><p class="text-xs text-red-600 mt-1" x-text="errors.selectedDate"></p></template>
                </div>

                <!-- Slots -->
                <template x-if="slots.length && selectedDate">
                    <div class="md:col-span-2">
                        <h3 class="text-gray-800 font-semibold mb-2">Available Time Slots</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <template x-for="slot in slots" :key="slot.slot_id">
                                <div class="rounded-lg p-4 transition hover:shadow-md border select-none relative min-h-[150px]"
                                     :class="slot.is_booked ? 'slot-unavailable' : (isSlotSelected(slot) ? 'slot-selected' : 'slot-available')"
                                     @click="!slot.is_booked && selectSingleSlot(slot)">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="font-semibold mb-1" :class="slot.is_booked ? 'text-gray-400' : ''" x-text="slot.name"></h4>
                                        <span class="pill pill-refund" :class="slot.is_booked ? 'opacity-50' : ''" x-text="'Refundable ₹ ' + money(slot.deposit)"></span>
                                    </div>
                                    <p class="text-sm mb-1" :class="slot.is_booked ? 'text-gray-400' : ''" x-text="slot.timings"></p>
                                    <p class="mt-2 font-bold text-lg" :class="slot.is_booked ? 'text-gray-400' : ''">
                                        <span class="muted text-sm font-normal">Rent:</span> ₹ <span x-text="money(slot.price)"></span>
                                    </p>
                                    <span x-show="isSlotSelected(slot) && !slot.is_booked" class="absolute top-2 right-2 text-xl">✔</span>
                                </div>
                            </template>
                        </div>

                        <div class="notice mt-3" x-show="selectedSlots.length">
                            <strong>Refundable deposit:</strong>
                            <span x-text="'₹ ' + money(totalDeposit)"></span> is added to your payment and will be returned after the function.<br>
                            If you take any items on rent (e.g., chairs, fans, thali, etc.), their charges will be
                            <strong>deducted from this deposit</strong>. Any balance will be refunded to you.
                        </div>

                        <template x-if="errors.selectedSlots"><p class="text-xs text-red-600 mt-2" x-text="errors.selectedSlots"></p></template>
                    </div>
                </template>
            </div>

            <!-- inline selected summary -->
            <div x-show="selectedSlots.length" class="text-center text-sm text-gray-600 mt-4">
                <template x-for="slot in selectedSlots" :key="slot.slot_id">
                    <div>
                        Selected: <span class="font-medium" x-text="slot.name"></span> (<span x-text="slot.timings"></span>)
                        — <span class="muted">Rent ₹</span><span x-text="money(slot.price)"></span>,
                        <span class="muted">Deposit ₹</span><span x-text="money(slot.deposit)"></span>
                    </div>
                </template>
            </div>

            <!-- hidden inputs (include discount + community) -->
            <input type="hidden" name="venue_id" :value="selectedVenueId">
            <input type="hidden" name="booking_date" :value="selectedDate">
            <input type="hidden" name="price" :value="netPayable">   <!-- NET (rent+deposit-discount) -->
            <input type="hidden" name="rent_total" :value="totalRent">
            <input type="hidden" name="deposit_total" :value="totalDeposit">
            <input type="hidden" name="discount" :value="appliedDiscount">
            <input type="hidden" name="payment_method" value="online">
            <!-- community is posted via the select name="community" -->

            <template x-for="slot in selectedSlots" :key="slot.slot_id">
                <input type="hidden" name="slot_ids[]" :value="slot.slot_id">
            </template>
<div>
                <label class="inline-flex items-start space-x-2">
                    <input type="checkbox" x-model="termsAccepted" name="terms_accepted"
                        class="border border-gray-300 rounded mt-1">
                    <span class="text-sm text-gray-700">
                        I accept the terms & conditions, including the refundable deposit policy.
                    </span>
                </label>
                <template x-if="errors.termsAccepted">
                    <p class="text-xs text-red-600 mt-1" x-text="errors.termsAccepted"></p>
                </template>
            </div>
            <!-- step 1 totals (no discount line here) -->
            <div class="pt-3 text-sm text-gray-600" x-show="selectedSlots.length">
                <div class="flex justify-between">
                    <span>Rent</span> <span class="font-medium">₹ <span x-text="money(totalRent)"></span></span>
                </div>
                <div class="flex justify-between">
                    <span>Refundable Deposit</span> <span class="font-medium">₹ <span x-text="money(totalDeposit)"></span></span>
                </div>
                <div class="divider"></div>
                <div class="flex justify-between text-gray-900">
                    <span class="font-semibold">Payable Now</span>
                    <span class="font-bold text-lg">₹ <span x-text="money(grossPayable)"></span></span>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" :disabled="selectedSlots.length === 0 || isLoading"
                        :class="selectedSlots.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#116631] hover:bg-green-900'"
                        class="w-full text-white py-3 rounded-md text-sm font-semibold transition flex justify-center items-center">
                    <span x-show="!isLoading">Proceed to Summary</span>
                    <span x-show="isLoading" class="spinner"></span>
                </button>
            </div>
        </form>

        <!-- STEP 2: summary (shows discount) -->
        <div x-show="step === 2" class="flex flex-col items-center">
            <div style="width:90%; padding:20px"
                 class="bg-white border border-gray-200 rounded-xl shadow p-8 max-w-md w-full space-y-4"
                 x-ref="bookingSummary">
                <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo" class="h-16 w-16 mx-auto mb-2">
                <h2 class="text-center text-xl font-bold">Booking Summary</h2>

                <div class="text-sm">
                    <p><strong>Name:</strong> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                    <p><strong>Venue:</strong> <span x-text="venueName"></span></p>
                    <template x-for="slot in selectedSlots" :key="slot.slot_id">
                        <p>• <span x-text="slot.name"></span> — <span x-text="slot.timings"></span></p>
                    </template>
                </div>

                <div class="divider"></div>

                <div class="flex justify-between items-center">
                    <span>Rent</span>
                    <span class="font-medium">₹ <span x-text="money(totalRent)"></span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Refundable Deposit</span>
                    <span class="font-medium">₹ <span x-text="money(totalDeposit)"></span></span>
                </div>

                <template x-if="appliedDiscount > 0">
                    <div class="flex justify-between items-center">
                        <span>Applied Discount (Dudhwala)</span>
                        <span class="font-medium text-green-700">− ₹ <span x-text="money(appliedDiscount)"></span></span>
                    </div>
                </template>

                <div class="divider"></div>

                <div class="flex justify-between items-center">
                    <span>Total Payable Now</span>
                    <span class="font-bold text-lg">₹ <span x-text="money(netPayable)"></span></span>
                </div>
                <p class="text-xs text-gray-500">Including applicable taxes</p>
            </div>

            <div class="flex w-full max-w-md mt-6 gap-4">
                <button @click="step = 1"
                        class="w-1/2 bg-gray-300 hover:bg-gray-400 text-gray-900 py-2 rounded">Previous</button>
                <button @click="makePayment" :disabled="isLoading"
                        :class="isLoading ? 'bg-green-800' : 'bg-[#116631] hover:bg-green-900'"
                        class="w-1/2 text-white py-2 rounded flex justify-center items-center">
                    <span x-show="!isLoading">Make Payment</span>
                    <span x-show="isLoading" class="spinner"></span>
                </button>
            </div>
        </div>

        <!-- STEP 3 (success) -->
        <div x-show="step === 3" class="flex flex-col items-center justify-center min-h-[340px] space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow p-10 text-center max-w-md w-full">
                <svg width="35" height="35" viewBox="0 0 24 24" fill="green" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                </svg>
                <h2 class="text-lg font-semibold text-green-700 mb-2">Payment Successful</h2>
                <p class="text-xl font-bold">Your hall has been booked.</p>
                <div class="mt-4 flex gap-4 justify-center">
                    <a href="{{ route('profile.edit') }}" class="bg-[#116631] hover:bg-green-900 text-white py-2 px-4 rounded">View Booking</a>
                    <a :href="invoiceUrl" target="_blank" class="bg-white border border-[#116631] text-[#116631] py-2 px-4 rounded" x-show="invoiceUrl">Download Invoice</a>
                </div>
            </div>
        </div>

       
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->any())
                Swal.fire({ icon:'error', html:`{!! implode('<br>', $errors->all()) !!}`, confirmButtonColor:'#d33' });
            @endif
        });
    </script>
</x-app-layout>
