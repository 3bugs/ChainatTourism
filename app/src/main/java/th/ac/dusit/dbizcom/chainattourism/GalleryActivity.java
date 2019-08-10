package th.ac.dusit.dbizcom.chainattourism;

import android.content.Context;
import android.content.Intent;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.support.v7.widget.Toolbar;
import android.widget.TextView;

import com.google.gson.Gson;

import java.util.ArrayList;
import java.util.List;

import th.ac.dusit.dbizcom.chainattourism.fragment.ImageFragment;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

public class GalleryActivity extends AppCompatActivity implements
        ImageFragment.ImageFragmentListener {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_gallery);

        Intent intent = getIntent();
        Place place = new Gson().fromJson(intent.getStringExtra("place_json"), Place.class);
        int currentImageIndex = intent.getIntExtra("current_index", 0);

        //setupToolbar(placeName);

        ViewPager viewPager = findViewById(R.id.view_pager);
        GalleryPagerAdapter adapter = new GalleryPagerAdapter(
                this,
                getSupportFragmentManager(),
                place.galleryImages
        );
        viewPager.setAdapter(adapter);
        viewPager.setCurrentItem(currentImageIndex);
    }

    private void setupToolbar(String placeName) {
        Toolbar toolbar = findViewById(R.id.toolbar);
        final TextView titleTextView = toolbar.findViewById(R.id.title_text_view);
        titleTextView.setText(placeName);
        titleTextView.setTextColor(getResources().getColor(android.R.color.transparent));
        setSupportActionBar(toolbar);
    }

    private static class GalleryPagerAdapter extends FragmentStatePagerAdapter {

        private Context mContext;
        private List<String> mImageFileNameList;

        GalleryPagerAdapter(Context context, FragmentManager fm, List<String> imageFileNameList) {
            super(fm);
            mContext = context;
            mImageFileNameList = imageFileNameList;
        }

        @Override
        public Fragment getItem(int position) {
            return ImageFragment.newInstance(mImageFileNameList.get(position));
        }

        @Override
        public int getCount() {
            return mImageFileNameList.size();
        }
    }
}
