import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EcoleComponent } from './ecole/ecole.component';
import { EleveComponent } from './eleve/eleve.component';
import { MatiereComponent } from './matiere/matiere.component';
import { NoteComponent } from './note/note.component';

const routes: Routes = [
  { path: '', redirectTo: 'ecole', pathMatch: 'full'},
  { path: 'ecole', component: EcoleComponent},
  { path: 'eleve', component: EleveComponent},
  { path: 'matiere', component: MatiereComponent},
  { path: 'note', component: NoteComponent},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdministrationRoutingModule { }
