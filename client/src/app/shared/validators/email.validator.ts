import { AbstractControl, ValidationErrors } from "@angular/forms";

export class EmailValidatorStrict {
    static emailStrictPattern: RegExp = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    static emailStrict(control: AbstractControl): ValidationErrors | null {
        const value = control.value;
        if (!value) return null;

        const isValid = EmailValidatorStrict.emailStrictPattern.test(value);

        return isValid ? null : { emailStrict: true};
    }
}