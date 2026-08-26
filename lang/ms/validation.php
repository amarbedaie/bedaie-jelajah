<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Ruangan :attribute mesti diterima.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'Ruangan :attribute mesti alamat URL yang sah.',
    'after' => 'Ruangan :attribute mesti tarikh selepas :date.',
    'after_or_equal' => 'Ruangan :attribute mesti tarikh pada atau selepas :date.',
    'alpha' => 'Ruangan :attribute hanya boleh mengandungi huruf.',
    'alpha_dash' => 'Ruangan :attribute hanya boleh mengandungi huruf, nombor, sengkang dan garis bawah.',
    'alpha_num' => 'Ruangan :attribute hanya boleh mengandungi huruf dan nombor.',
    'any_of' => 'The :attribute field is invalid.',
    'array' => 'The :attribute field must be an array.',
    'array_keys' => 'The :attribute field must only contain the following keys: :values.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'base64' => 'The :attribute field must be a valid Base64 string.',
    'before' => 'Ruangan :attribute mesti tarikh sebelum :date.',
    'before_or_equal' => 'Ruangan :attribute mesti tarikh pada atau sebelum :date.',
    'between' => [
        'array' => 'Ruangan :attribute mesti antara :min dan :max item.',
        'file' => 'Ruangan :attribute mesti antara :min dan :max kilobait.',
        'numeric' => 'Ruangan :attribute mesti antara :min dan :max.',
        'string' => 'Ruangan :attribute mesti antara :min dan :max aksara.',
    ],
    'boolean' => 'Ruangan :attribute mesti benar atau salah.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'Pengesahan :attribute tidak sepadan.',
    'contains' => 'The :attribute field is missing a required value.',
    'current_password' => 'Kata laluan tidak betul.',
    'date' => 'Ruangan :attribute mesti tarikh yang sah.',
    'date_equals' => 'Ruangan :attribute mesti tarikh yang sama dengan :date.',
    'date_format' => 'Ruangan :attribute tidak mengikut format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'Ruangan :attribute mesti ditolak.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'Ruangan :attribute dan :other mesti berbeza.',
    'digits' => 'Ruangan :attribute mesti :digits digit.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_contain' => 'The :attribute field must not contain any of the following: :values.',
    'doesnt_end_with' => 'Ruangan :attribute tidak boleh berakhir dengan salah satu daripada: :values.',
    'doesnt_start_with' => 'Ruangan :attribute tidak boleh bermula dengan salah satu daripada: :values.',
    'email' => 'Ruangan :attribute mesti alamat e-mel yang sah.',
    'encoding' => 'The :attribute field must be encoded in :encoding.',
    'ends_with' => 'Ruangan :attribute mesti berakhir dengan salah satu daripada: :values.',
    'enum' => 'Pilihan :attribute tidak sah.',
    'exists' => 'Pilihan :attribute tidak sah.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'Ruangan :attribute mesti diisi.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'Ruangan :attribute mesti imej.',
    'in' => 'Pilihan :attribute tidak sah.',
    'in_array' => 'The :attribute field must exist in :other.',
    'in_array_keys' => 'The :attribute field must contain at least one of the following keys: :values.',
    'integer' => 'Ruangan :attribute mesti nombor bulat.',
    'ip' => 'Ruangan :attribute mesti alamat IP yang sah.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'Ruangan :attribute mesti rentetan JSON yang sah.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'Ruangan :attribute mesti huruf kecil.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'Ruangan :attribute tidak boleh melebihi :max item.',
        'file' => 'Ruangan :attribute tidak boleh melebihi :max kilobait.',
        'numeric' => 'Ruangan :attribute tidak boleh melebihi :max.',
        'string' => 'Ruangan :attribute tidak boleh melebihi :max aksara.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'Ruangan :attribute mesti fail berjenis: :values.',
    'mimetypes' => 'Ruangan :attribute mesti fail berjenis: :values.',
    'min' => [
        'array' => 'Ruangan :attribute mesti sekurang-kurangnya :min item.',
        'file' => 'Ruangan :attribute mesti sekurang-kurangnya :min kilobait.',
        'numeric' => 'Ruangan :attribute mesti sekurang-kurangnya :min.',
        'string' => 'Ruangan :attribute mesti sekurang-kurangnya :min aksara.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'Ruangan :attribute mesti tiada.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'Pilihan :attribute tidak sah.',
    'not_regex' => 'Format :attribute tidak sah.',
    'numeric' => 'Ruangan :attribute mesti nombor.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'Ruangan :attribute mesti wujud.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'Ruangan :attribute dilarang.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_if_accepted' => 'The :attribute field is prohibited when :other is accepted.',
    'prohibited_if_declined' => 'The :attribute field is prohibited when :other is declined.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'Format :attribute tidak sah.',
    'required' => 'Ruangan :attribute wajib diisi.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'Ruangan :attribute wajib diisi apabila :other ialah :value.',
    'required_if_accepted' => 'Ruangan :attribute wajib diisi apabila :other diterima.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => 'Ruangan :attribute wajib diisi kecuali :other ialah :values.',
    'required_with' => 'Ruangan :attribute wajib diisi apabila :values wujud.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'Ruangan :attribute wajib diisi apabila :values tiada.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'Ruangan :attribute dan :other mesti sepadan.',
    'size' => [
        'array' => 'Ruangan :attribute mesti mengandungi :size item.',
        'file' => 'Ruangan :attribute mesti :size kilobait.',
        'numeric' => 'Ruangan :attribute mesti :size.',
        'string' => 'Ruangan :attribute mesti :size aksara.',
    ],
    'starts_with' => 'Ruangan :attribute mesti bermula dengan salah satu daripada: :values.',
    'string' => 'Ruangan :attribute mesti teks.',
    'timezone' => 'Ruangan :attribute mesti zon waktu yang sah.',
    'unique' => ':attribute ini telah digunakan.',
    'uploaded' => 'Ruangan :attribute gagal dimuat naik.',
    'uppercase' => 'Ruangan :attribute mesti huruf besar.',
    'url' => 'Ruangan :attribute mesti URL yang sah.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'Ruangan :attribute mesti UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
