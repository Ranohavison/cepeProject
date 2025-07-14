import { Component, OnInit } from '@angular/core';
import { Pagination } from '../../../models/pagination';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { SharedDataService } from '../../../core/services/shared-data.service';
import { TableCrudComponent } from '../../../core/components/table-crud/table-crud.component';
import { ApiService } from '../../../core/services/api.service';
import { animate, query, stagger, state, style, transition, trigger } from '@angular/animations';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-matiere',
  imports: [CommonModule, TableCrudComponent, ReactiveFormsModule],
  templateUrl: './matiere.component.html',
  styleUrl: './matiere.component.css',
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

export class MatiereComponent implements OnInit {
  isLoaded = false;
  position = 'default';
  isEditMode: boolean = false;
  nbrMatiere: Number = 0;
  columns: string[] = [];
  data: any[] = [];
  selectedItem: any;
  pagination: Pagination = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 0,
  };
  searchQuery: string = '';
  matiereForm: FormGroup;

  constructor( private fb: FormBuilder, private sharedDataService: SharedDataService, private apiService : ApiService) {
    this.matiereForm = this.fb.group({
      numMat: ['', [Validators.required, Validators.minLength(3)]],
      design: ['', [Validators.required, Validators.minLength(4)]],
      coef: ['', [Validators.required, Validators.min(0)]],
    });
  }

  get numMat() { return this.matiereForm.get('numMat');}
  get design() { return this.matiereForm.get('design');}
  get coef() { return this.matiereForm.get('coef');}

  move(direction: 'left' | 'right' | 'reset') {
    this.position = direction === 'reset' ? 'default' : direction;
  }

  initForm() {
    this.move('left');
    this.matiereForm.get('numMat')?.enable();
    this.matiereForm.patchValue({ numMat: '', design: '', coef: '' });
  }

  search(searchQuery: string) {
    this.searchQuery = searchQuery;
    this.getMatiere();
  }

  getMatiere() {
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery
    };

    this.apiService.getData('matiere/getAll?page=1', params).subscribe({
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
          (this.nbrMatiere =
            Number(this.pagination.total / this.pagination.per_page) + 1)
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des données :', err);
      },
    });
  }

  addMode() {
    this.matiereForm.get('numEcole')?.enable();
    this.isEditMode = false;
    this.move('right');
  }

  editMode() {
    this.matiereForm.patchValue({
      numMat: this.selectedItem.numMat,
      design: this.selectedItem.designMat,
      coef: this.selectedItem.coef,
    });
    this.matiereForm.get('numEcole')?.disable();
    this.isEditMode = true;
    this.move('right');
  }

  deleteItem() {
    if (
      confirm(
        `Êtes-vous sûr de vouloir supprimer la matière ${this.selectedItem.numMat} : ${this.selectedItem.design} ?`
      )
    ) {
      this.apiService
        .deleteItem('matiere/delete', this.selectedItem.numMat)
        .subscribe({
          next: (response) => {
            console.log('Suppression réussie', response);
            this.getMatiere();
            this.selectedItem = null;
            // Actualiser la liste ou faire une action post-suppression
          },
          error: (err) => {
            console.error('Erreur lors de la suppression', err);
          },
        });
    }
  }

  onSubmit(event: Event) {
    if (this.matiereForm.valid) {
      if (!this.isEditMode) {this.apiService.postData('matiere/create', this.matiereForm.value).subscribe({
        next: (response) => {
          console.log('Réponse du serveur : ', response);
          this.getMatiere();
          this.initForm();
        },
        error: (err) => {
          console.error('Erreur lors de la récupération des données :', err);
        },
      });
      } else {
        this.apiService.updateItem('matiere/update', this.selectedItem.numMat, this.matiereForm.value).subscribe({
          next: (response) => {
            console.log('Réponse du serveur : ', response);
            this.getMatiere();
            this.initForm();
          },
          error: (err) => {
            console.error('Erreur lors de la récupération des données :', err);
          },
        })
      }
    }  
  }

  ngOnInit(): void {
    this.sharedDataService.selectedItem$.subscribe((item) => {
      this.selectedItem = item;
    });
    this.getMatiere();
    setTimeout(() => {
      this.isLoaded = true;
    }, 300);
  }
}
