var fixedLength = 0; 
jQuery.validator.addMethod("filesize_max", function(value, element, param) {
    var isOptional = this.optional(element),
        file;

    if (isOptional) {
        return isOptional;
    }
    if ($(element).attr("type") === "file") {

        if (element.files && element.files.length) {

            file = element.files[0];
            //console.log(file.size);      
            return (file.size && file.size <= 52428800);
        }
    }
    return false;
}, "File size is too large.");

$.validator.addMethod('dimension', function(value, element, param) {
    if (element.files.length == 0) {
        return true;
    }
    var file = element.files[0];
    var width = height = 0;
    var tmpImg = new Image();
    var result = '';
    tmpImg.src = window.URL.createObjectURL(file);
    tmpImg.onload = function() {
        width = tmpImg.naturalWidth,
            height = tmpImg.naturalHeight;

        console.log(width);
        console.log(height);
        result = (width <= param[0] && height <= param[1]);
        console.log(result);
        return result;
    }
}, function() {
    return 'Please upload an image with maximum 100 x 100 pixels dimension'
});

jQuery.validator.addMethod("fixedDigits", function(value, element, param) {
    var isOptional = this.optional(element);
    fixedLength = param;

    if (isOptional) {
        return isOptional;
    }

    return ($(element).val().length <= param);
}, function() {
    return "Value cannot exceed " + fixedLength + " characters."
});

jQuery.validator.addMethod("extension", function(value, element, param) {
    param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
    return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, "Please select image with a valid extension (.jpg, .jpeg, .png, .gif, .svg)");

jQuery.validator.addMethod("import_extension", function(value, element, param) {
    param = typeof param === "string" ? param.replace(/,/g, '|') : "xls|xlsx";
    return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, "Please select file with a valid extension (.xls, .xlsx)");

jQuery.validator.addMethod("docextension", function(value, element, param) {
    param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
    return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, "Please select file with a valid extension (.jpg, .jpeg, .png, .doc, .docx, .pdf)");

jQuery.validator.addMethod("decimalPlaces", function(value, element) {
    return this.optional(element) || /^\d+(\.\d{0,2})?$/i.test(value);
}, "Please enter a value with maximum two decimal places.");

jQuery.validator.addMethod("alphanumeric", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9]+$/i.test(value);
}, "Please enter alphanumeric value.");

jQuery.validator.addMethod("alphanumericspace", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9\s]+$/i.test(value);
}, "Please enter alphanumeric value.");

jQuery.validator.addMethod("exactlength", function(value, element, param) {
    return this.optional(element) || value.length == param;
}, $.validator.format("Please enter exactly {0} characters."));

jQuery.validator.addMethod("lettersonly", function(value, element) {
    return this.optional(element) || /^[a-zA-Z\s]+$/i.test(value);
}, "Name can have alphabets and space only.");

jQuery.validator.addMethod("contact_number", function(value, element) {
    return this.optional(element) || /^\+[0-9]+[0-9\-]+[0-9]+$/i.test(value);
}, "Incorrect contact number format");

jQuery.validator.addMethod("zip_number", function(value, element) {
    return this.optional(element) || /^\+[0-9]+[0-9\-]+[0-9]+$/i.test(value);
}, "Incorrect zipcode number format");

jQuery.validator.addMethod("number_formate", function(value, element) {
    return this.optional(element) || /^\+[0-9]+[0-9\-]+[0-9]+$/i.test(value);
}, "Incorrect number format");

jQuery.validator.addMethod("non_whitespace", function(value, element) {
    return this.optional(element) || /^(?!\s*$).+/i.test(value);
}, "Incorrect value");

jQuery.validator.addMethod("check_content", function(value, el, param) {
    var content = $(el).summernote('code');
    content = $(content).text().replace(/\s+/g, '');

    return (content !== "");
}, "Incorrect value");

jQuery.validator.addMethod("correctPassword", function(value, element) {
    return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/i.test(value);
}, "Please fill minimum 6 character Password with uppercase, lowercase, special character and digit");

$.validator.addMethod("greaterThanDate", function(value, element, param) {
    var $otherElement = $(param);
    return new Date('1970-01-01T' + value + 'Z') > new Date('1970-01-01T' + $otherElement.val() + 'Z');
}, "End Time must be greater than start time");


jQuery.validator.addMethod("validate_email", function(value, element) {
    if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value)) {
        return true;
    } else {
        return false;
    }
}, "Please enter a valid Email.");


var form_validation = function() {
    // alert('enter');
    var e = function() {
        var form_validate = jQuery(".form-valide").validate({
            ignore: [".note-editor *", "password"],
            errorClass: "invalid-feedback animated fadeInDown",
            errorElement: "div",
            errorPlacement: function(e, a) {
                jQuery(a).closest(".form-group").append(e)
            },
            highlight: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
            },
            success: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
            },
            rules: {  
                "doc_email": {
                    required: !0,
                    validate_email: !0
                }, 
                "location_email": {
                    required: !0,
                    validate_email: !0
                },
                "location_url": {
                    required: !0
                },
                "email": {
                    required: !0,
                    validate_email: !0,
                    remote: APP_NAME + "/admin/admins/checkAdmins"
                },
                "password": {
                    required: !0,
                    minlength: 6,
                    correctPassword: !0
                },
                "confirm-password": {
                    required: !0,
                    equalTo: "#password",
                    correctPassword: !0
                }, 
                "doc_name": {
                    required: !0,
                   // lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                }, 
                "cat_name": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                }, 
                "name": {
                    required: !0,
                   // lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                },
                "product_name": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                },
                "brand_id": {
                    required: !0
                }, 
                "state_id[]": {
                    required: !0
                }, 
                "parent_id": {
                    required: !0
                }, 
                "category_id": {
                    required: !0
                }, 
                "type": {
                    required: !0
                }, 
                "dispensary_id": {
                    required: !0
                }, 
                "zipcode": {
                    required: !0,
                   // number: !0,
                    maxlength: 8,
                    minlength: 3
                },  
                "type_id": {
                    required: !0
                }, 
                "qty": {
                    required: !0
                }, 
                "image_url": {
                    required: !0
                }, 
                "product_url": {
                    required: !0
                }, 
                "prod_description": {
                    required: !0
                }, 
                "strain_id": {
                    required: !0
                }, 
                "product_sku": {
                    required: !0
                }, 
                "price": {
                    required: !0
                },
                "discount_price": {
                   // number_formate: !0
                },
                "dis_name": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                },
                "location_id": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 100,
                    minlength: 3
                },
                "description": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 1000,
                    minlength: 3
                },
                "address": {
                    required: !0,
                    //lettersonly: !0,
                    maxlength: 200,
                    minlength: 3
                },
                "phone_number": {
                    required: !0,
                    number: !0,
                    maxlength: 15,
                    minlength: 8
                }, 
                "mobile": {
                    required: !0,
                    remote: APP_NAME + "/admin/users/checkUsers",
                    number: !0,
                    maxlength: 15,
                    minlength: 8
                },  
                "cat_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    //required: !0
                     filesize_max: !0
                },
                "doc_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    //required: !0
                     filesize_max: !0
                },
                "filemanager_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    required: !0,
                    filesize_max: !0
                }, 
                "brand_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    //required: !0
                    // filesize_max: !0
                },
                "product_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    //required: !0
                    // filesize_max: !0
                },
                "dis_image": {
                    extension: "jpeg|png|jpg|gif|svg",
                    //required: !0
                    // filesize_max: !0
                },  
                "product_title": {
                    required: !0,
                    //remote: APP_NAME + "/admin/products/checkProduct",
                    maxlength: 50,
                    alphanumericspace: !0,
                    non_whitespace: !0
                },
                "brand_name": {
                    required: !0
                   // remote: APP_NAME + "/admin/brands/checkBrand",
                   // maxlength: 50,
                   // alphanumericspace: !0,
                   // non_whitespace: !0
                },
                "state_name": {
                    required: !0,
                   // remote: APP_NAME + "/admin/states/checkState",
                    maxlength: 50,
                    lettersonly: !0,
                    alphanumericspace: !0,
                    non_whitespace: !0
                },
            },
            messages: {  
                "doc_email": {
                    required: "Please provide email address",
                    validate_email: "Please enter a valid email address"
                }, 
                "location_email": {
                    required: "Please provide email address",
                    validate_email: "Please enter a valid email address"
                },
                "location_url": {
                    required: "Please provide email address"
                },
                "email": {
                    required: "Please provide email address",
                    validate_email: "Please enter a valid email address",
                    remote: "This email is already taken."
                },
                "password": {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 6 characters long"
                },
                "confirm-password": {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 6 characters long",
                    equalTo: "Please enter the same password as above"
                },   
                "brand_id": {
                    required: "Please provide company"
                },
                "state_id[]": {
                    required: "Please provide state"
                },  
                "parent_id": {
                    required: "Please provide parent"
                }, 
                "category_id": {
                    required: "Please provide category"
                }, 
                "type": {
                    required: "Please provide type"
                }, 
                "image_url": {
                    required: "Please provide image url"
                }, 
                "product_url": {
                    required: "Please provide image url"
                },
                "prod_description": {
                    required: "Please provide product description"
                },
                "dispensary_id": {
                    required: "Please provide location"
                }, 
                "zipcode": {
                    required: "Please provide zip code",
                   // number: "Please provide number only",
                    maxlength: "Your zip code max 8 digit long",
                    minlength: "Your zip code at least 3 digit long"
                    
                },  
                "type_id": {
                    required: "Please provide type"
                }, 
                "qty": {
                    required: "Please provide product qty"
                }, 
                "strain_id": {
                    required: "Please provide strain"
                }, 
                "product_sku": {
                    required: "Please provide sku"
                },
                 "price": {
                    required: "Please provide price"
                },
                 "discount_price": {
                    //number_formate: "Please provide number only"
                }, 
                "doc_name": {
                    required: "Please provide name",
                    //lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                }, 
                "cat_name": {
                    required: "Please provide name",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                },
                "name": {
                    required: "Please provide name",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                },
                "product_name": {
                    required: "Please provide name",
                    //lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                }, 
                 "phone_number": {
                    required: "Please provide phone number",
                    number: "Please provide number only",
                    maxlength: "Your mobile max 15 digit long",
                    minlength: "Your mobile at least 8 digit long"
                },
                 "mobile": {
                    required: "Please provide phone number",
                    number: "Please provide number only",
                    remote: "This mobile is already added another account",
                    maxlength: "Your mobile max 15 digit long",
                    minlength: "Your mobile at least 8 digit long"
                }, 
                "dis_name": {
                    required: "Please provide name",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                },
                "location_id": {
                    required: "Please provide location id",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 100 characters long",
                    minlength: "Your name at least 3 characters long"
                },
                "description": {
                    required: "Please provide description",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your description max 1000 characters long",
                    minlength: "Your description at least 3 characters long"
                },
                "address": {
                    required: "Please provide address",
                   // lettersonly: "Please provide lettersonly",
                    maxlength: "Your name max 200 characters long",
                    minlength: "Your name at least 3 characters long"
                },  
                "product_image": {
                    required: "Please provide product image"
                },
                "cat_image": {
                    required: "Please provide category image"
                },
                "doc_image": {
                    required: "Please provide doctor image"
                },
                "filemanager_image": {
                    required: "Please provide image"
                },
                "brand_image": {
                    required: "Please provide company image"
                },
                "dis_image": {
                    required: "Please provide location image"
                },  
                "product_title": {
                    required: "Please provide title abbreviation",
                   // remote: "This title is already added",
                },
                 "brand_name": {
                    required: "Please provide company name",
                   // remote: "This company name is already added",
                },
                "state_name": {
                    required: "Please provide state name",
                    lettersonly: "Please provide lettersonly",
                   // remote: "This state name is already added",
                },
            }
        })
    }
    return {
        init: function() {
            e(), jQuery(".js-select2").on("change", function() {
                jQuery(this).valid()
            })
        }
    }
}();
jQuery(function() {
    form_validation.init()
});
