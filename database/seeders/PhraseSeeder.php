<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\{Phrase, PhraseCategory};

class PhraseSeeder extends Seeder
{
    public function run(): void
    {
        // [category slug, khmer, romanization, en, zh, ko, is_emergency]
        $phrases = [
            ['greetings', 'សួស្តី',            'suosdei',          'Hello',                    '你好',        '안녕하세요',        false],
            ['greetings', 'អរគុណ',             'aw kun',           'Thank you',                '谢谢',        '감사합니다',        false],
            ['greetings', 'សុំទោស',            'som toh',          'Sorry / Excuse me',        '对不起',      '죄송합니다',        false],
            ['greetings', 'លាហើយ',             'lea haeuy',        'Goodbye',                  '再见',        '안녕히 가세요',     false],
            ['greetings', 'ខ្ញុំមិនយល់ទេ',      'knhom min yul te', "I don't understand",       '我不明白',    '이해하지 못했어요', false],
            ['food',      'ឆ្ងាញ់',             'chhnganh',         'Delicious',                '好吃',        '맛있어요',          false],
            ['food',      'សូមទឹកមួយ',          'som tuk muoy',     'Water, please',            '请给我水',    '물 주세요',         false],
            ['shopping',  'ថ្លៃប៉ុន្មាន?',       'tlai ponman?',     'How much is it?',          '多少钱？',    '얼마예요?',         false],
            ['shopping',  'ថ្លៃពេក',            'tlai pek',         'Too expensive',            '太贵了',      '너무 비싸요',       false],
            ['hotel',     'បន្ទប់ទឹកនៅឯណា?',    'bontub tuk nov ae na?', 'Where is the toilet?', '洗手间在哪里？', '화장실이 어디예요?', false],
            ['emergency', 'ជួយខ្ញុំផង',         'juoy knhom phong', 'Help me!',                 '请帮帮我',    '도와주세요',        true],
            ['emergency', 'ហៅប៉ូលិស',           'hav police',       'Call the police',          '请叫警察',    '경찰을 불러주세요', true],
        ];

        foreach ($phrases as [$cat, $km, $roman, $en, $zh, $ko, $emergency]) {
            $categoryId = PhraseCategory::where('slug', $cat)->value('id');

            Phrase::updateOrCreate(['khmer_text' => $km], [
                'phrase_category_id' => $categoryId,
                'romanization'       => $roman,
                'translations'       => compact('en', 'zh', 'ko'),
                'is_emergency'       => $emergency,
                'status'             => 'published',
            ]);
        }
    }
}