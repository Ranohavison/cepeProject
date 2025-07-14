import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { ApiService } from '../../../core/services/api.service';
import {
  animate,
  query,
  stagger,
  state,
  style,
  transition,
  trigger,
} from '@angular/animations';
import { SharedDataService } from '../../../core/services/shared-data.service';
import { Pagination } from '../../../models/pagination';
import { TableCrudComponent } from '../../../core/components/table-crud/table-crud.component';

@Component({
  selector: 'app-eleve',
  imports: [ReactiveFormsModule, CommonModule, TableCrudComponent],
  templateUrl: './eleve.component.html',
  styleUrl: './eleve.component.css',
  animations: [
    trigger('slide', [
      state('default', style({ transform: 'translate(0, 0' })),
      state('left', style({ transform: '' })),
      state('right', style({ transform: 'translateX(40rem)' })),
      transition('* <=> *', [animate('0.5s ease-in-out')]),
    ]),
    trigger('pageAnimations', [
      transition(':enter', [
        query('.animated-element', [
          style({ opacity: 0, transform: 'translateY(-20px)' }),
          stagger('100ms', [
            animate(
              '300ms ease-out',
              style({ opacity: 1, transform: 'translateY(0)' })
            ),
          ]),
        ]),
      ]),
    ]),
  ],
})
export class EleveComponent implements OnInit {
  isLoaded = false;
  position = 'default';
  eleveForm: FormGroup;
  selectedItem: any;
  columns: string[] = [];
  data: any[] = [];
  isEditMode: boolean = false;
  nbrEleve: Number = 0;
  pagination: Pagination = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 0,
  };
  searchQuery: string = '';

  constructor(
    private fb: FormBuilder,
    private apiService: ApiService,
    private sharedDataService: SharedDataService
  ) {
    this.eleveForm = this.fb.group({
      numEleve: ['', [Validators.required, Validators.minLength(3)]],
      nom: ['', [Validators.required, Validators.minLength(4)]],
      prenom: ['', [Validators.required, Validators.minLength(4)]],
      numEcole: ['', [Validators.required, Validators.minLength(3)]],
    });
  }

  get numEleve() {
    return this.eleveForm.get('numEleve');
  }

  get nom() {
    return this.eleveForm.get('nom');
  }

  get prenom() {
    return this.eleveForm.get('prenom');
  }

  get numEcole() {
    return this.eleveForm.get('numEcole');
  }

  move(direction: 'left' | 'right' | 'reset') {
    this.position = direction === 'reset' ? 'default' : direction;
  }

  initForm() {
    this.move('left');
    this.eleveForm.get('numEleve')?.enable();
    this.eleveForm.patchValue({ numEleve: '', nom: '', prenom: '', numEcole: ''});
  }

  search(searchQuery: string) {
    this.searchQuery = searchQuery;
    this.getEleves();
  }

  getEleves() {
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery
    };

    this.apiService.getData('eleve/getAll?page=1', params).subscribe({
      next: (response) => {
        console.log(response);

        this.columns = response.columns;
        this.data = response.data;
        (this.pagination = {
          current_page: Number(response.pagination.current_page),
          per_page: Number(response.pagination.per_page),
          total: Number(response.pagination.total),
          last_page: Number(response.pagination.last_page),
        }),
          (this.nbrEleve =
            Number(this.pagination.total / this.pagination.per_page) + 1);
        // this.move('left');
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des données :', err);
      },
    });
  }

  add() {
    this.eleveForm.get('numEleve')?.enable();
    this.isEditMode = false;
    this.move('right');
  }

  edit() {
    this.eleveForm.patchValue({
      numEleve: this.selectedItem.numEleve,
      nom: this.selectedItem.nom,
      prenom: this.selectedItem.prenom,
      numEcole: this.selectedItem.numEcole,
    });
    this.eleveForm.get('numEleve')?.disable();
    this.isEditMode = true;
    this.move('right');
  }

  onSubmit(event: Event) {
    event.preventDefault();

    if (this.eleveForm.valid) {
      if (!this.isEditMode) {
        this.apiService
          .postData('eleve/create', this.eleveForm.value)
          .subscribe({
            next: (response) => {
              console.log('Réponse du serveur : ', response);
              this.initForm();
              this.getEleves();
            },
            error: (err) => {
              console.error(
                'Erreur lors de la récupération des données :',
                err
              );
            },
          });
      } else {
        this.apiService.updateItem('eleve/update', this.selectedItem.numEleve, this.eleveForm.value).subscribe({
          next: (response) => {
            console.log('Réponse du serveur : ', response);
            this.getEleves();
            this.initForm();
          },
          error: (err) => {
            console.error('Erreur lors de la récupération des données :', err);
          },
        })
      }
    } else {
      console.log('Form invalid');
    }
  }

  deleteItem() {
    if (
      confirm(
        `Êtes-vous sûr de vouloir supprimer l'élève ${this.selectedItem.numEleve} : ${this.selectedItem.nom} ${this.selectedItem.prenom} ?`
      )
    ) {
      this.apiService
        .deleteItem('eleve/delete', this.selectedItem.numEleve)
        .subscribe({
          next: (response) => {
            console.log('Suppression réussie', response);
            this.getEleves();
            this.selectedItem = null;
            // Actualiser la liste ou faire une action post-suppression
          },
          error: (err) => {
            console.error('Erreur lors de la suppression', err);
          },
        });
    }
  }

  ngOnInit(): void {
    this.sharedDataService.selectedItem$.subscribe((item) => {
      this.selectedItem = item;
    });
    this.getEleves();
    setTimeout(() => {
      this.isLoaded = true;
    }, 300);
  }
}
