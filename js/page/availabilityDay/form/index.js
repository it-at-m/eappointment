import React, { PropTypes } from 'react'

import Board from '../layouts/board'
import * as Inputs from './inputs'

const { Label, FormGroup, Controls } = Inputs

const renderBody = (data, onChange) => {
    return (
        <form>
            <fieldset>
                <FormGroup>
                    <Label title="(optionale Angabe zur Kennzeichnung des Termins)">Anmerkung</Label>
                    <Controls>
                        <Inputs.Text name="description" value="" />
                    </Controls>
                </FormGroup>
                <FormGroup>
                    <Label>Typ</Label>
                    <Controls>
                        <Inputs.Select name="type" options={[
                                {value: "", name: "--Bitte wählen--"},
                                {value: "openinghours", name: "Spontankunden"},
                                {value: "appointment", name: "Terminkunden"},
                            ]} {...{ onChange }}/>
                    </Controls>
                </FormGroup>

                <FormGroup> 
                    <Label>Serie</Label>
                    <Controls>
                        <Inputs.Select name="repeat" options={[
                            {value: "0", name: "einmaliger Termin"},
                            {value: "-1", name: "jede Woche"},
                            {value: "-2", name: "alle 2 Wochen"},
                            {value: "-3", name: "alle 3 Wochen"},
                            {value: "1", name: "jede 1. Woche im Monat"},
                            {value: "2", name: "jede 2. Woche im Monat"},
                            {value: "3", name: "jede 3. Woche im Monat"},
                            {value: "4", name: "jede 4. Woche im Monat"},
                            {value: "5", name: "jede 5. Woche im Monat"}
                        ]} />
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label>Wochentage:</Label>
                    <Controls>
                        <Inputs.CheckboxGroup name="weekdays" inline={true} boxes={[
                            {value: "Mo", label: "Mo"},
                            {value: "Di", label: "Di"},
                            {value: "Mi", label: "Mi"},
                            {value: "Do", label: "Do"},
                            {value: "Fr", label: "Fr"},
                            {value: "Sa", label: "Sa"},
                            {value: "So", label: "So"}
                        ]} />
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label>Uhrzeit:</Label>
                    <Controls>
                        von <Inputs.Text name="time_start_hour" />:<Inputs.Text name="time_start_min" /> Uhr
                        bis <Inputs.Text name="time_end_hour" />:<Inputs.Text name="time_end_hour" /> Uhr
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label>Datum:</Label>
                    <Controls>
                        Startdatum: <Inputs.Text name="date_start" />
                        Enddatum: <Inputs.Text name="date_end" />
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label title="(Abstand zweier aufeinander folgender Termine)">Zeitschlitz:</Label>
                    <Controls>
                        <Inputs.Text name="slotTimeInMinutes" attributes={{maxLength: 3}} /> min.
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label title="(wie in der Dienstleistungsdatenbank konfiguriert)">{" "}</Label>
                    <Controls>
                        <Label>
                            <Inputs.Checkbox name="multiple_slot_allowed" />
                            Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen 
                        </Label>
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label>Buchbar:</Label>
                    <Controls>
                        von <Inputs.Text name="open_from" width="1" />
                        bis <Inputs.Text name="open_to" width="1" /> Tage im voraus
                    </Controls>
                </FormGroup>
            </fieldset>

            <fieldset>
                <FormGroup>
                    <Label>Insgesamt</Label>
                    <Controls>
                        <Inputs.Text name="workstationCount_intern" attributes={{maxLength: "2", width: "1"}} />
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label title="wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.">Callcenter</Label>
                    <Controls>
                        <Inputs.Select name="workstationCount_callcenter" options={[
                            { value: "0", name: "0" },
                            { value: "1", name: "1" },
                            { value: "2", name: "2" },
                            { value: "3", name: "3" },
                            { value: "4", name: "4" },
                            { value: "5", name: "5" },
                            { value: "6", name: "6" },
                            { value: "7", name: "7" },
                            { value: "8", name: "8" },
                            { value: "9", name: "9" },
                            { value: "10", name: "10" },
                            { value: "11", name: "11" },
                            { value: "12", name: "12" },
                            { value: "13", name: "13" },
                            { value: "14", name: "14" },
                            { value: "15", name: "15" },
                            { value: "16", name: "16" },
                            { value: "17", name: "17" },
                            { value: "18", name: "18" },
                            { value: "19", name: "19" },
                            { value: "20", name: "20" },
                            { value: "21", name: "21" },
                        ]}/>
                    </Controls>
                </FormGroup>

                <FormGroup>
                    <Label title="wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden.">Internet</Label>
                    <Controls>
                        <Inputs.Select name="workstationCount_public" options={[
                            { value: "0", name: "0" },
                            { value: "1", name: "1" },
                            { value: "2", name: "2" },
                            { value: "3", name: "3" },
                            { value: "4", name: "4" },
                            { value: "5", name: "5" },
                            { value: "6", name: "6" },
                            { value: "7", name: "7" },
                            { value: "8", name: "8" },
                            { value: "9", name: "9" },
                            { value: "10", name: "10" },
                            { value: "11", name: "11" },
                            { value: "12", name: "12" },
                            { value: "13", name: "13" },
                            { value: "14", name: "14" },
                            { value: "15", name: "15" },
                            { value: "16", name: "16" },
                            { value: "17", name: "17" },
                            { value: "18", name: "18" },
                            { value: "19", name: "19" },
                            { value: "20", name: "20" },
                            { value: "21", name: "21" },
                        ]}/>
                    </Controls>
                </FormGroup>
            </fieldset>

            <div className="form-actions">
                <button className="button-delete" type="delete" value="delete">Löschen</button>
                <div className="right">
                    <button className="button-save" type="save" value="save">Speichern</button>
                </div>
            </div>
        </form>
    )
}

const AvailabilityForm = (props) => {
    const { data } = props
    const onChange = (name, value) => console.log('onChange', name, value)

    return <Board title="Öffnungszeit bearbeiten" headerRight="" body={renderBody(data, onChange)} footer=""/>
}

AvailabilityForm.propTypes = {
    data: PropTypes.object
}

export default AvailabilityForm
