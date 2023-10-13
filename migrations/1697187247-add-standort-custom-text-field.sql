ALTER TABLE buerger
ADD custom_text_field VARCHAR(255); 

ALTER TABLE standort
ADD custom_text_field_label VARCHAR(255),
    custom_text_field_active int(5),
    custom_text_field_required int(5);