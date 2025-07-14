import { Component, OnInit } from '@angular/core';
import { SharedDataService } from '../../../core/services/shared-data.service';
import { animate, query, stagger, state, style, transition, trigger,} from '@angular/animations';
import { CommonModule } from '@angular/common';
import { Pagination } from '../../../models/pagination';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ApiService } from '../../../core/services/api.service';
import { TableCrudComponent } from '../../../core/components/table-crud/table-crud.component';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-note',
  imports: [CommonModule, TableCrudComponent, ReactiveFormsModule],
  templateUrl: './note.component.html',
  styleUrl: './note.component.css',
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
export class NoteComponent implements OnInit {
  isLoaded = false;
  isEditMode = false;
  position = 'default';
  selectedItem: any;
  columns: string[] = [];
  data: any[] = [];
  pagination: Pagination = { current_page: 1, per_page: 10, total: 0, last_page: 0,};
  nbrNotPMat: Number = 0;
  searchQuery: string = '';
  noteForm: FormGroup;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private sharedDataService: SharedDataService,
    private fb: FormBuilder,
    private apiService: ApiService
  ) {
    this.noteForm = this.fb.group({
      anneeScolaire: ['', [Validators.required, Validators.minLength(9)]],
      numEleve: ['', [Validators.required, Validators.minLength(3)]],
      numMat: ['', [Validators.required, Validators.minLength(3)]],
      note: ['', [Validators.required, Validators.min(0)]],
    });
    this.noteForm.get('anneeScolaire')?.disable();
    this.noteForm.patchValue({ anneeScolaire: '2022-2023' });
  }

  get anneeScolaire() { return this.noteForm.get('anneeScolaire');}

  get numEleve() { return this.noteForm.get('numEleve');}

  get numMat() { return this.noteForm.get('numMat');}

  get note() { return this.noteForm.get('note');}

  move(direction: 'left' | 'right' | 'reset') { this.position = direction === 'reset' ? 'default' : direction;}

  initForm() {
    this.move('left');
    this.noteForm.get('numEleve')?.enable();
    this.noteForm.get('numEcole')?.enable();
    this.noteForm.patchValue({ anneeScolaire: '', numEleve: '', numMat: '' , note: ''});
  }

  search(searchQuery: string) {
    this.searchQuery = searchQuery;
    this.getNote();
  }

  toPage(page: number) {
    this.pagination.current_page = page
    this.getNote();
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { page: this.pagination.current_page},
      queryParamsHandling: 'merge',
    })
  }

  nextPage(){
    this.route.queryParams.subscribe(params => {
      this.pagination.current_page = Number(params['page']) + 1;
    })
    this.getNote();
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { page: this.pagination.current_page},
      queryParamsHandling: 'merge',
    })
  }

  previousPage(){
    this.route.queryParams.subscribe(params => {
      if (this.pagination.current_page > 1) this.pagination.current_page = Number(params['page']) - 1;
    })
    this.getNote();
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { page: this.pagination.current_page},
      queryParamsHandling: 'merge',
    })
  }

  getNote() {
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery
    };

    this.apiService.getData(`note/getAll`, params).subscribe({
      next: (response) => {
        this.columns = response.columns;
        this.data = response.data;
        (this.pagination = {
          current_page: Number(response.pagination.current_page),
          per_page: Number(response.pagination.per_page),
          total: Number(response.pagination.total),
          last_page: Number(response.pagination.last_page),
        }),
          (this.nbrNotPMat =
            Number(this.pagination.total / this.pagination.per_page) + 1),
          this.move('left');
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des données :', err);
      },
    });
  }

  addMode() {
    this.noteForm.patchValue({ anneeScolaire: '2022-2023' });
    this.noteForm.get('numEleve')?.enable();
    this.noteForm.get('numMat')?.enable();
    this.isEditMode = false;
    this.move('right');
  }

  edit() {
    this.noteForm.patchValue({
      anneeScolaire: this.selectedItem.anneeScolaire,
      numEleve: this.selectedItem.numEleve,
      numMat: this.selectedItem.numMat,
      note: this.selectedItem.note,
    });
    this.noteForm.get('numEleve')?.disable();
    this.noteForm.get('numMat')?.disable();
    this.isEditMode = true;
    this.move('right');
  }

  deleteItem() {
    if (
      confirm(
        `Êtes-vous sûr de vouloir supprimer ce note ?`
      )
    ) {
      this.apiService
        .deleteItem('note/delete', `${this.selectedItem.numEleve}/${this.selectedItem.numMat}`)
        .subscribe({
          next: (response) => {
            console.log('Suppression réussie', response);
            this.getNote();
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
    console.log(this.noteForm.value);
    

    if (!this.isEditMode) {
      this.apiService.postData('note/create', this.noteForm.value).subscribe({
        next: (response) => {
          console.log('Réponse du serveur : ', response);
          this.getNote();
          this.initForm();
        },
        error: (err) => {
          console.error('Erreur lors de la récupération des données :', err);
        },
      });
    } else {
      this.apiService.updateItem('note/update', this.selectedItem.numEleve, {numEleve: this.selectedItem.numEleve, numMat: this.selectedItem.numMat, note: this.noteForm.get('note')?.value}).subscribe({
        next: (response) => {
          console.log('Réponse du serveur : ', response);
          this.getNote();
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
    this.getNote();
    setTimeout(() => {
      this.isLoaded = true;
    }, 300);
  }
}
