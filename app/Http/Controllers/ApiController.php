<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiController extends Controller
{

    /**
     * Get Articles From Our Database
     */
    public function getArticles(Request $request)
    {
        // $apiKey = $request->header('X-API-Key');
        $apiKey = $request->query('api_key');


        // Debugging: Log API key
        \Log::info('API Key: ' . $apiKey);

        // Check if the API key is valid
        $user = User::where('api_key', $apiKey)->first();

        // Debugging: Log user
        \Log::info('User: ' . json_encode($user));

        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Invalid API key.'], 401);
        }

        $query = Article::query();

        // Apply filters based on request parameters
        if ($category = $request->input('category')) {
          $query->whereIn('category', (array) $category);
        }

        if ($source = $request->input('source')) {
            $query->whereIn('source', (array) $source);
        }

        // Filter for multiple authors
        $authors = $request->input('authors');
        if ($authors && is_array($authors)) {
            $query->whereIn('author', $authors);
        }

        // Filter for multiple content keywords
        $contentKeywords = $request->input('content_keywords');
        if ($contentKeywords && is_array($contentKeywords)) {
            foreach ($contentKeywords as $contentKeyword) {
                $query->where('content', 'like', '%' . $contentKeyword . '%');
            }
        }

        if ($datePublished = $request->input('date_published')) {
            $query->whereDate('date_published', '=', $datePublished);
        }

        // Retrieve articles
        $articles = $query->get();

        return response()->json($articles);
    }

    /**
     * Fetch Articles for regularly updated
     */

     public function fetchArticles() {
        // Fetch articles from selected data sources and store them locally

        $newsAPIArticles = $this->fetchNewsAPIArticles();
        $openNewsArticles = $this->fetchNYTArticles();
        $newsCredArticles = $this->fetchGuardianArticles();

    }

    /**
     *  Fetch NewsAPI Articles
     */
    private function fetchNewsAPIArticles() {

      $apiKey = '33d95dc9383340bd9c1ed6146d723300'; // Replace with your News API key

      // Get the current date and time
      $currentDateTime = Carbon::now();

      // Get the previous date
      $previousDate = $currentDateTime->subDay();

      // Format the date as needed
      $todayDate = $previousDate->format('Y-m-d');

      $response = Http::get("https://newsapi.org/v2/everything?q=tesla&from=".$todayDate."&sortBy=publishedAt&apiKey={$apiKey}");

       $data = $response->json();

       if ($response->successful() && isset($data['articles'])) {
         // Store articles in the database
         foreach ($data['articles'] as $article) {

           // Generate a unique slug from the title
           $baseSlug = Str::slug($article['title']);
           $slug = $baseSlug;
           $counter = 1;

           // Check if the generated slug already exists
           while (Article::where('slug', $slug)->exists()) {
               $slug = $baseSlug . '-' . $counter;
               $counter++;
           }

           Article::updateOrCreate(
               ['title' => $article['title']],
               [
                   'content' => $article['content'] ?? null,
                   'category' => $article['category'] ?? null,
                   'source' => $article['source']['name'] ?? null,
                   'author' => $article['author'] ?? null,
                   'published_at' => $article['publishedAt'] ? Carbon::parse($article['publishedAt']) : null,
                   'slug' => $slug,
                   'description' => $article['description'] ?? null,
                   'url' => $article['url'] ?? null,
               ]
             );
         }
       }


   }

    /**
     * Fetch NewsAPI Articles
     */
     private function fetchNYTArticles() {

       $apiKey = '9HBCf2O6ypZfUipXA0zhWqGZxZxSXCbn'; // Replace with your News API key

       // Get the current date and time
       $currentDateTime = Carbon::now();

       // Get the previous date
       $previousDate = $currentDateTime->subDay();

       // Format the date as needed
       $todayDate = $previousDate->format('Y-m-d');

       $response = Http::get("https://api.nytimes.com/svc/search/v2/articlesearch.json?api-key={$apiKey}&pub_date=".$todayDate);

        $data = $response->json();

        if ($response->successful() && isset($data['response']['docs'])) {

          foreach ($data['response']['docs'] as $article) {

            // Generate a unique slug from the title
            $baseSlug = Str::slug($article['headline']['main']);
            $slug = $baseSlug;
            $counter = 1;

            // Check if the generated slug already exists
            while (Article::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
             $title = $article['headline']['main'];
             $content = $article['snippet'];
             $category = null;
             $source = 'New York Times';
             $author = $article['byline']['original'] ?? null;
             $slug = $slug;
             $description = $article['abstract'];
             $url = $article['web_url'];
             $published_at = $article['pub_date'];

             Article::updateOrCreate(
                 ['title' => $title],
                 [
                     'content' => $content ?? null,
                     'category' => null,
                     'source' => $source ?? null,
                     'author' => $author ?? null,
                     'published_at' => $published_at ? Carbon::parse($published_at) : null,
                     'slug' => $slug,
                     'description' => $description ?? null,
                     'url' => $url ?? null,
                 ]
               );
         }

        }

    }

    /**
     * Fetch From GUARDIAN
     */
      private function fetchGuardianArticles() {
        $apiKey = '456f9ab1-9152-4add-937d-f7a7e06c877c';

        // Get the current date and time
        $currentDateTime = Carbon::now();

        // Get the previous date
        $previousDate = $currentDateTime->subDay();

        // Format the date as needed
        $todayDate = $previousDate->format('Y-m-d');

        $url = "https://content.guardianapis.com/search?q=debate&tag=politics/politics&from-date=".$todayDate."&api-key={$apiKey}";

        $response = Http::get($url);

        $data = $response->json();

        if ($response->successful() && isset($data['response']['results'])) {
          foreach ($data['response']['results'] as $result) {

            // Generate a unique slug from the title
            $baseSlug = Str::slug($result['webTitle']);
            $slug = $baseSlug;
            $counter = 1;

            // Check if the generated slug already exists
            while (Article::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

              Article::updateOrCreate(
                  ['title' => $result['webTitle']],
                  [
                      'content' => $result['id'],
                      'category' => $result['sectionName'],
                      'source' => 'The Guardian',
                      'author' => $result['fields']['byline'] ?? null,
                      'slug' => $slug,
                      'description' => $result['fields']['trailText'] ?? null,
                      'url' => $result['webUrl'],
                      'published_at' => $result['webPublicationDate'] ? Carbon::parse($result['webPublicationDate']) : null,
                  ]
              );
          }
        }


      }
}
