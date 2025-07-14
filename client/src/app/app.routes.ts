import { Routes } from '@angular/router';
import { AccueilComponent } from './features/accueil/accueil.component';
import { ResultatComponent } from './features/resultat/resultat.component';

export const routes: Routes = [
    { path: '', redirectTo: '/accueil', pathMatch: 'full'},
    { path: 'accueil', component: AccueilComponent},
    { path: 'resultat', component: ResultatComponent},
    { path: 'administration', loadChildren: () => import('./features/administration/administration.module').then(m => m.AdministrationModule) },
    { path: '**', redirectTo: '/accueil'},
];