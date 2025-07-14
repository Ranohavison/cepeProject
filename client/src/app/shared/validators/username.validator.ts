import { AbstractControl, ValidationErrors, ValidatorFn } from "@angular/forms";


export class UsernameValidator {
    static emailStrictPattern: RegExp = /^[a-zA-Z0-9.@+\-_]*$/;

    static usernameStrict(control: AbstractControl): ValidationErrors | null {
        const value = control.value;
        if (!value) return null;

        const isValid = UsernameValidator.emailStrictPattern.test(value);

        return isValid ? null : { usernameStrict: true};
    }
}