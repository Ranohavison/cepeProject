import { AbstractControl, ValidationErrors, ValidatorFn } from "@angular/forms";

export class PasswordValidator {
    static validPassword(control: AbstractControl): ValidationErrors | null {
        const value = control.value || '';
        
        const errors: ValidationErrors = {};

        if (!/[a-z]/.test(value)) {errors['lowercase'] = 'le mot de passe doit contenir au moins une chiffre minuscule.'; return Object.keys(errors).length > 0 ? errors : null;}

        if (!/[A-Z]/.test(value)) {errors['uppercase'] = 'le mot de passe doit contenir au moins une chiffre majuscule.'; return Object.keys(errors).length > 0 ? errors : null;}

        if (!/[0-9]/.test(value)) { errors['digit'] = 'Le mot de passe doit contenir au moins un chiffre.';  return Object.keys(errors).length > 0 ? errors : null;}

        if (!/[-_@$!%*?&]/.test(value)) { errors['specialChar'] = 'Le mot de passe doit contenir au moins un caractère spécial (-_@$!%*?&)'; return Object.keys(errors).length > 0 ? errors : null;}

        if (value.length < 8) { errors['minLength'] = 'Le mot de passe doit contenir au moins 8 caractères.'; return Object.keys(errors).length > 0 ? errors : null;}

        return Object.keys(errors).length > 0 ? errors : null;
    }
}

export class PasswordMatchValidator {
    static matchPasword(password1Key: string, password2Key: string) : ValidatorFn {
        return (group: AbstractControl) : ValidationErrors | null => { 
            const password1 = group.get(password1Key)?.value;
            const password2 = group.get(password2Key)?.value;

            if (password1 !== password2) {
                return { passwordsMissmatch: 'Les mots de passe ne correspondent pas.'};
            }
            return null;
        }
    }
}