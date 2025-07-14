import {
  animate,
  query,
  stagger,
  state,
  style,
  transition,
  trigger,
} from '@angular/animations';
import { Component, ElementRef, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { TableCrudComponent } from '../../../core/components/table-crud/table-crud.component';
import { ApiService } from '../../../core/services/api.service';
import { Pagination } from '../../../models/pagination';
import { SharedDataService } from '../../../core/services/shared-data.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-ecole',
  imports: [ReactiveFormsModule, TableCrudComponent, CommonModule],
  templateUrl: './ecole.component.html',
  styleUrl: './ecole.component.css',
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

export class EcoleComponent implements OnInit {
  isLoaded = false;
  position = 'default';
  columns: string[] = [];
  data: any[] = [];
  nbrEcole: Number = 0;
  selectedItem: any;
  isEditMode: boolean = false;
  pagination: Pagination = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 0,
  };
  searchQuery: string = '';
  addForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    private el: ElementRef,
    private apiService: ApiService,
    private sharedDataService: SharedDataService
  ) {
    this.addForm = this.fb.group({
      numEcole: ['', [Validators.required, Validators.minLength(3)]],
      design: ['', [Validators.required, Validators.minLength(4)]],
      adresse: ['', [Validators.required, Validators.minLength(6)]],
    });
  }

  changeMode() {
    this.isEditMode = !this.isEditMode;
  }

  get numEcole() {
    return this.addForm.get('numEcole');
  }

  get design() {
    return this.addForm.get('design');
  }

  get adresse() {
    return this.addForm.get('adresse');
  }

  move(direction: 'left' | 'right' | 'reset') {
    this.position = direction === 'reset' ? 'default' : direction;
  }

  initForm() {
    this.move('left');
    this.addForm.get('numEcole')?.enable();
    this.addForm.patchValue({ numEcole: '', design: '', adresse: '' });
  }

  search(searchQuery: string) {
    this.searchQuery = searchQuery;
    this.getEcoles();
  }

  getEcoles() {
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery
    };
    // console.log(params);

    this.apiService.getData('ecole/getAll', params).subscribe({
      next: (response) => {
        this.columns = response.columns;
        this.data = response.data;
        (this.pagination = {
          current_page: Number(response.pagination.current_page),
          per_page: Number(response.pagination.per_page),
          total: Number(response.pagination.total),
          last_page: Number(response.pagination.last_page),
        }),
          (this.nbrEcole =
            Number(this.pagination.total / this.pagination.per_page) + 1),
          this.move('left');
        console.log(this.data);
        
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des données :', err);
      },
    });
  }

  addMode() {
    this.addForm.get('numEcole')?.enable();
    this.isEditMode = false;
    this.move('right');
  }

  edit() {
    this.addForm.patchValue({
      numEcole: this.selectedItem.numEcole,
      design: this.selectedItem.design,
      adresse: this.selectedItem.adresse,
    });
    this.addForm.get('numEcole')?.disable();
    this.isEditMode = true;
    this.move('right');
  }

  deleteItem() {
    if (
      confirm(
        `Êtes-vous sûr de vouloir supprimer l'école ${this.selectedItem.numEcole} : ${this.selectedItem.design} ?`
      )
    ) {
      this.apiService
        .deleteItem('ecole/delete', this.selectedItem.numEcole)
        .subscribe({
          next: (response) => {
            console.log('Suppression réussie', response);
            this.getEcoles();
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
    event.preventDefault();

    if (!this.isEditMode) {
      this.apiService.postData('ecole/create', this.addForm.value).subscribe({
        next: (response) => {
          console.log('Réponse du serveur : ', response);
          this.getEcoles();
          this.initForm();
        },
        error: (err) => {
          console.error('Erreur lors de la récupération des données :', err);
        },
      });
    } else {
      this.apiService.updateItem('ecole/update', this.selectedItem.numEcole, this.addForm.value).subscribe({
        next: (response) => {
          console.log('Réponse du serveur : ', response);
          this.getEcoles();
          this.initForm();
        },
        error: (err) => {
          console.error('Erreur lors de la récupération des données :', err);
        },
      })
    }
  }

  ngOnInit(): void {
    this.sharedDataService.selectedItem$.subscribe((item) => {
      this.selectedItem = item;
    });
    this.getEcoles();
    setTimeout(() => {
      this.isLoaded = true;
    }, 300);
  }
}
