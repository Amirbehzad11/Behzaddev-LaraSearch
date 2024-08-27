Usage
1. Add the Searchable trait to your model
First, use the Searchable trait in your model:


use YourVendor\LaravelSearchable\Searchable;

class TestModel extends Model
{
    use HasFactory, Searchable;

    // ...
}


2. Define searchable fields
Specify which fields of the model can be searched:

protected $searchable = ['filed1', 'filed2', 'filed3', 'filed4'];

3. Define filterable fields
Specify which fields can be used for filtering:

protected $filterable = ['statusfiled'];

4. Define searchable relations
Specify related models and their fields that can be searched:

protected $searchableRelations = [
    'relation1.relationTorelation1.relationTorelationTorelation1' => ['first_number', 'second_number', 'third_character', 'fourth_number'],
    'relation2' => ['code'],
    'relation3.relationTorelation3' => ['full_name'],
];

5. Define date searchable relations
Specify date fields in related models that can be used for date-based searches:

protected $dateSearchableRelations = [
    'relation1' => 'created_at'
];

Full Example
Here's a complete example of how your model might look:

class TestModel extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'filed1',
        'filed2',
        'filed3',
        'filed4',
        'statusfiled'
    ];

    protected $searchable = ['filed1', 'filed2', 'filed3', 'filed4'];
    protected $filterable = ['statusfiled'];
    protected $searchableRelations = [
        'relation1.relationTorelation1.relationTorelationTorelation1' => ['first_number', 'second_number', 'third_character', 'fourth_number'],
        'relation2' => ['code'],
        'relation3.relationTorelation3' => ['full_name'],
    ];
    protected $dateSearchableRelations = [
        'relation1' => 'created_at'
    ];
}
