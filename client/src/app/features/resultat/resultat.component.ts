import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../core/services/api.service';
import { TableCrudComponent } from '../../core/components/table-crud/table-crud.component';
import { Pagination } from '../../models/pagination';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-resultat',
  imports: [CommonModule, TableCrudComponent, FormsModule],
  templateUrl: './resultat.component.html',
  styleUrl: './resultat.component.css',
})
export class ResultatComponent implements OnInit {
  columns: string[] = [];
  searchQuery: string = '';
  data: any[] = [];
  options = ['tous', 'admis en 6ème', 'admis', 'délibération', 'recalé'];
  numberPerMention = {
    sixieme: 0,
    admis: 0,
    deliberation: 0,
    recale: 0,
  };
  selectedValue = this.options[0];
  pagination: Pagination = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 0,
  };

  constructor(private apiService: ApiService) {}

  downloadPdf(row: any) {
    this.apiService.generatePdf(row.numero);
  }

  onChangeValue(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    // console.log(value);
    if (value !== 'tous') this.searchQuery = value; 
    else this.searchQuery = '';
    this.getData();
  }

  search(searchQuery: string) {
    this.searchQuery = searchQuery;
    // console.log(searchQuery);    
    this.getData();
  }

  getData() {
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
    };
    // console.log(params);

    this.apiService.getData('result', params).subscribe({
      next: (response) => {
        this.columns = response.columns;
        this.data = response.data;
        this.numberPerMention = {
          sixieme: Number(response.numberPerMention.sixieme),
          admis: Number(response.numberPerMention.admis),
          deliberation: Number(response.numberPerMention.deliberation),
          recale: Number(response.numberPerMention.recale),
        };
        this.pagination = {
          current_page: Number(response.pagination.current_page),
          per_page: Number(response.pagination.per_page),
          total: Number(response.pagination.total),
          last_page: Number(response.pagination.last_page),
        };
        console.log(this.data);
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des données :', err);
      },
    });
  }

  ngOnInit(): void {
    this.getData();
  }
}
