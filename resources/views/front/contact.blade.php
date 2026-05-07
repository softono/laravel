@extends('layouts.main')
@section('title')
    Contact
@endsection
@section('content')
    <h4 class="fw-bold py-3 mb-4">Contact</h4>
    <div class="card card-default color-palette-box">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 ">
                    {{ view('common/message_alert') }}
                    <form class="ajax-contact-form" id="form" method="post" action="{{ route('contact-process') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="body" for="basic-icon-default-fullname">Name <span
                                            class="text-danger">*</span></label>
                                    <div class="">
                                        <input type="text" class="form-control simple" placeholder="Name" name="name"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="body" for="basic-icon-default-fullname">Email <span
                                            class="text-danger">*</span></label>
                                    <div class="">
                                        <input type="email" class="form-control simple" placeholder="Email" name="email"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="body" for="basic-icon-default-fullname">Subject <span
                                            class="text-danger">*</span></label>
                                    <div class="">
                                        <input type="text" class="form-control simple" placeholder="Subject"
                                            name="subject" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="body" for="basic-icon-default-fullname">Message <span
                                            class="text-danger">*</span></label>
                                    <div class="">
                                        <textarea name="message" placeholder="Message" class="form-control simple" required></textarea><br>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                {{ view('common/recaptcha') }}
                            </div>
                            <div class="col-lg-3">
                                <button type="submit" class="btn btn-primary text-white"
                                    style="background-color:#685dd8;">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--Bootstrap Tables-->
@endsection
@push('scripts')
    <script type="text/javascript">
        jQuery.validator.addMethod("noDisposableEmail", v => !["mailinator.com", "tempmail.com", "10minutemail.com",
            "guerrillamail.com", "fakeinbox.com"
        ].includes((v.split('@')[1] || "").toLowerCase()), "Disposable email addresses are not allowed.");
        documentReady(function() {
            $('.ajax-contact-form').validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2
                    },
                    email: {
                        required: true,
                        email: true,
                        noDisposableEmail: true
                    },
                    subject: {
                        required: true,
                        minlength: 5
                    },
                    message: {
                        required: true,
                        minlength: 10
                    }
                },
                messages: {
                    name: {
                        required: "Please enter your name",
                        minlength: "Your name must consist of at least 2 characters"
                    },
                    email: {
                        required: "Please enter a valid email address",
                        email: "Please enter a valid email address",
                        noDisposableEmail: "Please enter a valid email"
                    },
                    subject: {
                        required: "Please enter a subject",
                        minlength: "Your subject must consist of at least 5 characters"
                    },
                    message: {
                        required: "Please enter a message",
                        minlength: "Your message must consist of at least 10 characters"
                    }
                },
                submitHandler: function(form) {
                    app.ajaxForm(form);
                    try {
                        grecaptcha.reset();
                    } catch (e) {}
                }
            })
        });
    </script>
@endpush
