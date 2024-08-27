How It Works

use trait in model

class TestModel extends Model

    use HasFactory ,Searchable;
	
    protected $fillable =[
        'technical_manager_id',
        'sabaf_code',
        'bazdidfani_id',
        'status',
        'company_id'
    ];

    protected $searchable = [];
    protected $filterable = ['status', 'company_id'];
    protected $searchableRelations = [
        'bazdidfani.truckInfo.truck' => ['first_number','second_number','third_character','fourth_number'],
        'bazdidfani'=>['code'],
        'bazdidfani.Driver.drivers_info'=>['full_name'],
        'bazdidfani.technicalManagerCompany.technical_manager.user.personal'=>['full_name'],
    ];

    protected $dateSearchableRelations = [
        'bazdidfani' => 'created_at'
    ];
